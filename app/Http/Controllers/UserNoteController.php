<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHandlerRam;
use App\Models\UserNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class UserNoteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum'); // or 'auth:api' if you're using passport
    }

    private function successResponse($data, $message = '', $code = 200)
    {
        return ResponseHandlerRam::success(
            data: $data,
            message: $message,
            statusCode: $code
        );
    }

    private function failureResponse($message, $code = 400, $forceViewMessageDetails = false)
    {
        return ResponseHandlerRam::error(
            forceViewMessageDetails: $forceViewMessageDetails,
            message: $message,
            statusCode: $code
        );
    }

    /**
     * Store a new note - 100% SAFE FROM XSS & INJECTION
     * Notes are stored as PLAIN TEXT only
     */
    public function store(Request $request)
    {
        try {
            $userId = Auth::id();

            if (!$userId) {
                return $this->failureResponse('Unauthorized', 401);
            }

            $validated = $request->validate([
                'order_id'       => 'required|integer|exists:orders,id',
                'note'           => 'required|string|max:1000',
                'customer_note'  => 'sometimes|boolean',
            ]);

            // FORCE PLAIN TEXT: Strip ALL HTML tags to prevent XSS
            $cleanNote = strip_tags($validated['note']);

            // Remove control characters (extra safety)
            $cleanNote = trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $cleanNote));

            $note = UserNote::create([
                'user_id'       => $userId,
                'order_id'      => $validated['order_id'],
                'note'          => $cleanNote,
                'customer_note' => $validated['customer_note'] ?? false,
            ]);

            // Return the safely cleaned note
            $note->note = $cleanNote;

            return $this->successResponse($note, 'Note created successfully', 201);

        } catch (ValidationException $e) {
            return $this->failureResponse($e->errors(), 422, true);
        } catch (QueryException $e) {
            Log::error('Note creation failed: ' . $e->getMessage(), [
                'user_id' => $userId ?? null,
                'request' => $request->all()
            ]);
            return $this->failureResponse('Failed to create note. Database error.', 500);
        } catch (\Exception $e) {
            Log::error('Unexpected error in store note: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return $this->failureResponse('An unexpected error occurred.', 500);
        }
    }

    /**
     * Get all notes for an order - XSS PROTECTED ON OUTPUT
     */
    public function getAll(Request $request)
    {
        try {
            $orderId = $request->input('order_id');

            if (!$orderId || !ctype_digit((string)$orderId)) {
                return $this->failureResponse('Valid Order ID is required', 400);
            }

            $userId = Auth::id();
            if (!$userId) {
                return $this->failureResponse('Unauthorized', 401);
            }

            $notes = UserNote::where('order_id', $orderId)
                ->orderBy('id', 'desc')
                ->get();

            // Sanitize output: escape HTML but preserve line breaks
            $notes->transform(function ($note) {
                $note->note = nl2br(e($note->note)); // e() = htmlspecialchars(), nl2br for <br>
                // If you want plain text without <br>:
                // $note->note = e($note->note);
                return $note;
            });

            return $this->successResponse($notes, 'Notes retrieved successfully');

        } catch (QueryException $e) {
            Log::error('Failed to retrieve notes: ' . $e->getMessage(), [
                'order_id' => $orderId ?? null
            ]);
            return $this->failureResponse('Database error while retrieving notes.', 500);
        } catch (\Exception $e) {
            Log::error('Unexpected error in getAll notes: ' . $e->getMessage());
            return $this->failureResponse('An unexpected error occurred.', 500);
        }
    }
}