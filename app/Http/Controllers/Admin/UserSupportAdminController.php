<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserAdminSupportMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserSupportAdminController extends Controller
{
    /**
     * Display Customer Service / Live Support Console.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        // Fetch distinct users who have support messages
        $conversations = UserAdminSupportMessage::select('user_id', DB::raw('MAX(created_at) as last_msg_at'))
            ->groupBy('user_id')
            ->orderBy('last_msg_at', 'desc')
            ->get()
            ->map(function ($row) {
                $user = User::withTrashed()->find($row->user_id);
                if (!$user) return null;

                $lastMsg = UserAdminSupportMessage::where('user_id', $user->id)->latest()->first();
                $unread = UserAdminSupportMessage::where('user_id', $user->id)
                    ->where('sender_type', 'user')
                    ->where('is_read_by_admin', false)
                    ->count();

                $user->last_message = $lastMsg;
                $user->unread_count = $unread;
                return $user;
            })
            ->filter();

        if ($search) {
            $s = strtolower($search);
            $conversations = $conversations->filter(function ($u) use ($s) {
                return str_contains(strtolower($u->display_name), $s) || 
                       str_contains(strtolower($u->account_id ?? ''), $s) ||
                       str_contains(strtolower($u->phone ?? ''), $s);
            });
        }

        $selectedUser = null;
        $messages = collect();

        if ($conversations->isNotEmpty()) {
            $selectedId = $request->input('user_id', $conversations->first()->id);
            $selectedUser = User::withTrashed()->find($selectedId) ?? $conversations->first();

            // Mark user messages as read by admin
            UserAdminSupportMessage::where('user_id', $selectedUser->id)
                ->where('sender_type', 'user')
                ->where('is_read_by_admin', false)
                ->update(['is_read_by_admin' => true, 'read_at' => now()]);

            $messages = UserAdminSupportMessage::where('user_id', $selectedUser->id)
                ->orderBy('created_at', 'asc')
                ->get();
        }

        $totalUnread = UserAdminSupportMessage::where('sender_type', 'user')
            ->where('is_read_by_admin', false)
            ->count();

        return view('admin.support.index', compact('conversations', 'selectedUser', 'messages', 'totalUnread'));
    }

    /**
     * Show specific user's chat.
     */
    public function showUserChat($userId)
    {
        return redirect()->route('admin.support.index', ['user_id' => $userId]);
    }

    /**
     * Admin replies to user message.
     */
    public function reply(Request $request, $userId)
    {
        $user = User::withTrashed()->findOrFail($userId);
        $admin = auth()->user();

        $msgText = $request->input('message');
        $type = 'text';
        $mediaUrl = null;

        if ($request->hasFile('image') || $request->hasFile('file') || $request->hasFile('screenshot')) {
            $file = $request->file('image') ?: ($request->file('file') ?: $request->file('screenshot'));
            $filename = 'admin_reply_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $destDir = public_path('uploads/admin_support_for_user');
            if (!file_exists($destDir)) {
                @mkdir($destDir, 0777, true);
            }
            $file->move($destDir, $filename);
            $mediaUrl = 'uploads/admin_support_for_user/' . $filename;
            $type = 'image';
        }

        if (empty($msgText) && empty($mediaUrl)) {
            return back()->with('error', 'Please enter a reply or upload an image.');
        }

        UserAdminSupportMessage::create([
            'user_id' => $user->id,
            'admin_id' => $admin ? $admin->id : null,
            'sender_type' => 'admin',
            'type' => $type,
            'message' => $msgText,
            'media_url' => $mediaUrl,
            'is_read_by_admin' => true,
            'is_read_by_user' => false,
        ]);

        return redirect()->route('admin.support.index', ['user_id' => $user->id])
            ->with('success', "Reply sent to {$user->display_name} successfully!");
    }
}
