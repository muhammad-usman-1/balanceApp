@extends('layouts.admin')
@section('content')
@can('duration_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route('admin.durations.create') }}">
                {{ trans('global.add') }} {{ trans('cruds.duration.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('cruds.duration.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable datatable-Duration">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('cruds.duration.fields.id') }}
                        </th>
                        <th>
                            {{ trans('cruds.duration.fields.title') }}
                        </th>
                        <th>
                            {{ trans('cruds.duration.fields.description') }}
                        </th>
                        <th>
                            {{ trans('cruds.duration.fields.no_of_weeks') }}
                        </th>
                        <th>
                            {{ trans('cruds.duration.fields.created_at') }}
                        </th>
                        <th>
                            {{ trans('cruds.duration.fields.updated_at') }}
                        </th>
                       
                        <th>
                             Actions
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($durations as $key => $duration)
                        <tr data-entry-id="{{ $duration->id }}">
                            <td>

                            </td>
                            <td>
                                {{ $duration->id ?? '' }}
                            </td>
                            <td>
                                {{ $duration->title ?? '' }}
                            </td>
                            <td>
                                {{ $duration->description ?? '' }}
                            </td>
                            <td>
                                {{ $duration->no_of_weeks ?? '' }}
                            </td>
                            <td>
                                {{ $duration->created_at ?? '' }}
                            </td>
                            <td>
                                {{ $duration->updated_at ?? '' }}
                            </td>
                          
                            <td>
                                @can('duration_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.durations.show', $duration->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @can('duration_edit')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.durations.edit', $duration->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcan

                                @can('duration_delete')
                                    <form action="{{ route('admin.durations.destroy', $duration->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
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
@can('duration_delete')
  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('admin.durations.massDestroy') }}",
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
  let table = $('.datatable-Duration:not(.ajaxTable)').DataTable({ buttons: dtButtons })
  $('a[data-toggle="tab"]').on('shown.bs.tab click', function(e){
      $($.fn.dataTable.tables(true)).DataTable()
          .columns.adjust();
  });
  
})

</script>
@endsection