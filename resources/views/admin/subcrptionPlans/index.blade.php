@extends('layouts.admin')
@section('content')
@can('subcrption_plan_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route('admin.subcrption-plans.create') }}">
                {{ trans('global.add') }} {{ trans('cruds.subcrptionPlan.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('cruds.subcrptionPlan.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable datatable-SubcrptionPlan">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('cruds.subcrptionPlan.fields.id') }}
                        </th>
                        <th>
                            {{ trans('cruds.subcrptionPlan.fields.title') }}
                        </th>
                        <th>
                            {{ trans('cruds.subcrptionPlan.fields.description') }}
                        </th>
                        <th>
                            Price
                        </th>
                        <th>
                            No of Weeks
                        </th>
                        <th>
                            {{ trans('cruds.subcrptionPlan.fields.meal_count') }}
                        </th>
                        <th>
                            {{ trans('cruds.subcrptionPlan.fields.snack_count') }}
                        </th>
                        <th>
                            {{ trans('cruds.subcrptionPlan.fields.is_active') }}
                        </th>
                        <th>
                            {{ trans('cruds.subcrptionPlan.fields.created_at') }}
                        </th>
                        <th>
                            {{ trans('cruds.subcrptionPlan.fields.updated_at') }}
                        </th>

                        <th>
                             Actions
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($subcrptionPlans as $key => $subcrptionPlan)
                        <tr data-entry-id="{{ $subcrptionPlan->id }}">
                            <td>

                            </td>
                            <td>
                                {{ $subcrptionPlan->id ?? '' }}
                            </td>
                            <td>
                                {{ $subcrptionPlan->title ?? '' }}
                            </td>
                            <td>
                                {{ $subcrptionPlan->description ?? '' }}
                            </td>
                            <td>
                                {{ $subcrptionPlan->price ?? '' }}
                            </td>
                            <td>
                                {{ $subcrptionPlan->no_of_weeks ?? '' }}
                            </td>
                            <td>
                                {{ $subcrptionPlan->meal_count ?? '' }}
                            </td>
                            <td>
                                {{ $subcrptionPlan->snack_count ?? '' }}
                            </td>
                            <td>
                                <span style="display:none">{{ $subcrptionPlan->is_active ?? '' }}</span>
                                <input type="checkbox" disabled="disabled" {{ $subcrptionPlan->is_active ? 'checked' : '' }}>
                            </td>
                            <td>
                                {{ $subcrptionPlan->created_at ?? '' }}
                            </td>
                            <td>
                                {{ $subcrptionPlan->updated_at ?? '' }}
                            </td>

                            <td>
                                @can('subcrption_plan_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.subcrption-plans.show', $subcrptionPlan->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @can('subcrption_plan_edit')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.subcrption-plans.edit', $subcrptionPlan->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcan

                                @can('subcrption_plan_delete')
                                    <form action="{{ route('admin.subcrption-plans.destroy', $subcrptionPlan->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="submit" class="btn btn-xs btn-danger" value="{{ trans('global.delete') }}">
                                    </form>
                                @endcan

                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>



@endsection
@section('scripts')
@parent
<script>
    $(function () {
  let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons)
@can('subcrption_plan_delete')
  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('admin.subcrption-plans.massDestroy') }}",
    className: 'btn-danger',
    action: function (e, dt, node, config) {
      var ids = $.map(dt.rows({ selected: true }).nodes(), function (entry) {
          return $(entry).data('entry-id')
      });

      if (ids.length === 0) {
        alert('{{ trans('global.datatables.zero_selected') }}')

        return
      }

      if (confirm('{{ trans('global.areYouSure') }}')) {
        $.ajax({
          headers: {'x-csrf-token': _token},
          method: 'POST',
          url: config.url,
          data: { ids: ids, _method: 'DELETE' }})
          .done(function () { location.reload() })
      }
    }
  }
  dtButtons.push(deleteButton)
@endcan

  $.extend(true, $.fn.dataTable.defaults, {
    orderCellsTop: true,
    order: [[ 1, 'desc' ]],
    pageLength: 100,
  });
  let table = $('.datatable-SubcrptionPlan:not(.ajaxTable)').DataTable({ buttons: dtButtons })
  $('a[data-toggle="tab"]').on('shown.bs.tab click', function(e){
      $($.fn.dataTable.tables(true)).DataTable()
          .columns.adjust();
  });

})

</script>
@endsection
