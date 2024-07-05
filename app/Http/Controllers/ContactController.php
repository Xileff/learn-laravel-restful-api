<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Http\Resources\ContactCollection;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use App\Models\User;
use GuzzleHttp\Psr7\Response;
use Illuminate\Database\Eloquent\Builder;
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

    public function delete(int $id): JsonResponse
    {
        $user = Auth::user();
        $contact = $this->queryContact($id, $user);

        $contact->delete();

        return response()->json([
            'data' => true,
        ]);
    }

    public function search(Request $request): ContactCollection
    {
        $user = Auth::user();
        $page = $request->input('page', 1);
        $size = $request->input('size', 10);

        // SELECT * FROM contacts WHERE user_id = ?
        $contacts = Contact::where('user_id', $user->id);

        $contacts = $contacts->where(function (Builder $builder) use ($request) {
            $name = $request->input('name');

            // AND (WHERE first_name LIKE $name OR WHERE last_name LIKE $name)
            if ($name) {
                $builder->where(function (Builder $builder) use ($name) {
                    $builder->orWhere('first_name', 'LIKE', "%$name%");
                    $builder->orWhere('last_name', 'LIKE', "%$name%");
                });
            }

            // AND WHERE email LIKE $email
            $email = $request->input('email');
            if ($email) {
                $builder->where('email', 'LIKE', "%$email%");
            }

            // AND WHERE phone LIKE $phone
            $phone = $request->input('phone');
            if ($phone) {
                $builder->where('phone', 'LIKE', "%$phone%");
            }
        });

        $contacts = $contacts->paginate(perPage: $size, page: $page);

        return new ContactCollection($contacts);
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
