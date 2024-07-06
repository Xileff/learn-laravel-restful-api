<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddressRequest;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    public function create(string $contactId, AddressRequest $request): JsonResponse
    {
        // Verify if user owns contact
        $user = Auth::user();
        $contact = $this->queryContact($contactId, $user);

        // Get data
        $data = $request->validated();

        // Create address
        $address = new Address($data);
        $address->contact_id = $contact->id;
        $address->save();

        // Response
        $result = new AddressResource($address);
        return $result->response()->setStatusCode(201);
    }

    public function get(string $contactId, string $addressId): AddressResource
    {
        $user = Auth::user();
        $contact = $this->queryContact($contactId, $user);

        $address = $this->queryAddress($addressId, $contact->id);
        return new AddressResource($address);
    }

    public function update(string $contactId, string $addressId, AddressRequest $request): AddressResource
    {
        $user = Auth::user();
        $contact = $this->queryContact($contactId, $user);
        $address = $this->queryAddress($addressId, $contact->id);

        $data = $request->validated();
        $address->fill($data);
        $address->save();

        return new AddressResource($address);
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

    public function queryAddress(string $addressId, string $contactId): Address
    {
        $address = Address::where('id', $addressId)->where('contact_id', $contactId)->first();
        if (!$address) {
            $responseJson = response()->json([
                'errors' => [
                    'message' => [
                        'not found'
                    ]
                ]
            ])->setStatusCode(404);
            throw new HttpResponseException($responseJson);
        }
        return $address;
    }
}
