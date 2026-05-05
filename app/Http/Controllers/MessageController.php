<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Support\TopbarData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MessageController extends Controller
{
    public function index()
    {
        $messages = Message::orderBy('created_at', 'desc')->paginate(15);

        if (Message::where('status', 'unread')->exists()) {
            Message::where('status', 'unread')->update(['status' => 'read']);
            app(TopbarData::class)->forgetNotifications();
        }
        
        return view('messages.index', compact('messages'));
    }

    public function show(Message $message)
    {
        $message->markAsRead();
        return view('messages.show', compact('message'));
    }

    public function compose()
    {
        return view('messages.compose');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:10',
            'origin_page' => 'required|string|max:255',
        ]);

        // Determine action (send or draft)
        $action = $request->input('action', 'send');

        $message = Message::create([
            ...$validated,
            'status' => $action === 'draft' ? 'draft' : 'read',
            'response' => null,
            'responded_at' => $action === 'draft' ? null : now(),
            'responded_by' => auth()->id(),
        ]);

        // Send email if action is 'send'
        if ($action === 'send') {
            try {
                Mail::raw($validated['message'], function ($mail) use ($validated) {
                    $mail->to($validated['email'])
                        ->subject($validated['subject']);
                });
                
                $successMessage = 'Message sent successfully!';
            } catch (\Exception $e) {
                $successMessage = 'Message created but failed to send email. You can try resending later.';
            }
        } else {
            $successMessage = 'Message saved as draft.';
        }

        return redirect()->route('messages.show', $message)
            ->with('toast-success', $successMessage);
    }

    public function respond(Request $request, Message $message)
    {
        $validated = $request->validate([
            'response' => 'required|string|min:10',
        ]);

        $message->respond($validated['response']);

        // Optionally send email response to the sender
        if ($request->has('send_email') && $request->send_email) {
            try {
                Mail::raw($validated['response'], function ($mail) use ($message) {
                    $mail->to($message->email)
                        ->subject('Re: ' . $message->subject);
                });
            } catch (\Exception $e) {
                // Log error but don't fail the response save
            }
        }

        return redirect()->route('messages.show', $message)
            ->with('toast-success', 'Response saved successfully!');
    }

    public function destroy(Message $message)
    {
        $message->delete();

        return redirect()->route('messages.index')
            ->with('toast-success', 'Message deleted successfully!');
    }

    public function delete(Message $message)
    {
        return $this->destroy($message);
    }

    public function storeFromFrontend(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:10',
        ]);

        $validated['origin_page'] = $request->input('origin_page', 'frontend');

        Message::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Your message has been received. We will respond shortly.',
        ]);
    }
}
