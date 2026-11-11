<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAddressRequest;
use App\Http\Requests\UpdateAddressRequest;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AddressController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return AddressResource::collection(
            $request->user()->addresses()->latest()->get()
        );
    }

    public function store(StoreAddressRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $isDefault = ($data['is_default'] ?? false) || !$user->addresses()->exists();

        if ($isDefault) {
            $user->addresses()->update(['is_default' => false]);
        }

        $data['is_default'] = $isDefault;

        $address = $user->addresses()->create($data);
        $address->refresh();

        return (new AddressResource($address))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Address $address): AddressResource
    {
        $this->ensureOwner($request, $address);

        return new AddressResource($address);
    }

    public function update(UpdateAddressRequest $request, Address $address): AddressResource
    {
        $this->ensureOwner($request, $address);

        $data = $request->validated();

        if (!empty($data['is_default'])) {
            $request->user()->addresses()
                ->where('id', '!=', $address->id)
                ->update(['is_default' => false]);
        }

        $address->update($data);

        return new AddressResource($address->fresh());
    }

    public function destroy(Request $request, Address $address): JsonResponse
    {
        $this->ensureOwner($request, $address);

        $wasDefault = $address->is_default;

        $address->delete();

        if ($wasDefault) {
            $request->user()->addresses()->latest()->first()
                    ?->update(['is_default' => true]);
        }

        return response()->json(null, 204);
    }

    private function ensureOwner(Request $request, Address $address): void
    {
        abort_if($address->user_id !== $request->user()->id, 404);
    }
}
