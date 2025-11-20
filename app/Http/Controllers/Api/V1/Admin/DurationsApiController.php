<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDurationRequest;
use App\Http\Requests\UpdateDurationRequest;
use App\Http\Resources\Admin\DurationResource;
use App\Models\Duration;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DurationsApiController extends Controller
{
    public function index()
    {
        return new DurationResource(Duration::all());
    }

    public function store(StoreDurationRequest $request)
    {
        $duration = Duration::create($request->all());

        return (new DurationResource($duration))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Duration $duration)
    {
        return new DurationResource($duration);
    }

    public function update(UpdateDurationRequest $request, Duration $duration)
    {
        $duration->update($request->all());

        return (new DurationResource($duration))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function destroy(Duration $duration)
    {
        $duration->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
