<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyDurationRequest;
use App\Http\Requests\StoreDurationRequest;
use App\Http\Requests\UpdateDurationRequest;
use App\Models\Duration;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DurationsController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('duration_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $durations = Duration::all();

        return view('admin.durations.index', compact('durations'));
    }

    public function create()
    {
        abort_if(Gate::denies('duration_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.durations.create');
    }

    public function store(StoreDurationRequest $request)
    {
        $duration = Duration::create($request->all());

        return redirect()->route('admin.durations.index');
    }

    public function edit(Duration $duration)
    {
        abort_if(Gate::denies('duration_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.durations.edit', compact('duration'));
    }

    public function update(UpdateDurationRequest $request, Duration $duration)
    {
        $duration->update($request->all());

        return redirect()->route('admin.durations.index');
    }

    public function show(Duration $duration)
    {
        abort_if(Gate::denies('duration_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.durations.show', compact('duration'));
    }

    public function destroy(Duration $duration)
    {
        abort_if(Gate::denies('duration_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $duration->delete();

        return back();
    }

    public function massDestroy(MassDestroyDurationRequest $request)
    {
        $durations = Duration::find(request('ids'));

        foreach ($durations as $duration) {
            $duration->delete();
        }

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
