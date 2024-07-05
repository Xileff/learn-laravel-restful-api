<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
{
    public function create(ContactRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = Auth::user();

        $contact = new Contact($data);
        $contact->user_id = $user->id;
        $contact->save();

        $result = new ContactResource($contact);
        return $result->response()->setStatusCode(201);
    }

    public function get(int $id): ContactResource
    {
        $user = Auth::user();
        $contact = $this->queryContact($id, $user);

        return new ContactResource($contact);
    }

    public function update(int $id, ContactRequest $request): ContactResource
    {
        $user = Auth::user();
        $contact = $this->queryContact($id, $user);

        $data = $request->validated();
        $contact->fill($data);
        $contact->save();

        return new ContactResource($contact);
    }

    public function queryContact(int $id, User $user): Contact
    {
        $contact = Contact::where('id', $id)->where('user_id', $user->id)->first();
        if (!$contact) {
            $responseJson = response()->json([
                'errors' => [
                    'message' => [
                        'not found'
                    ]
                ]
            ])->setStatusCode(404);
            throw new HttpResponseException($responseJson);
        }

        return $contact;
    }
}
