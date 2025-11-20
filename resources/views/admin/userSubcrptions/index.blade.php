@extends('layouts.admin')
@section('content')
@can('user_subcrption_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route('admin.user-subcrptions.create') }}">
                {{ trans('global.add') }} {{ trans('cruds.userSubcrption.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('cruds.userSubcrption.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable datatable-UserSubcrption">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('cruds.userSubcrption.fields.id') }}
                        </th>
                        <th>
                            {{ trans('cruds.userSubcrption.fields.selected_days') }}
                        </th>
                        <th>
                            {{ trans('cruds.userSubcrption.fields.start_date') }}
                        </th>
                        <th>
                            {{ trans('cruds.userSubcrption.fields.end_date') }}
                        </th>
                        <th>
                            {{ trans('cruds.userSubcrption.fields.user') }}
                        </th>
                        <th>
                            {{ trans('cruds.userSubcrption.fields.subcrption_plans') }}
                        </th>
                        <th>
                            {{ trans('cruds.userSubcrption.fields.duration') }}
                        </th>
                        <th>
                            {{ trans('cruds.userSubcrption.fields.price') }}
                        </th>
                        <th>
                            {{ trans('cruds.userSubcrption.fields.payment') }}
                        </th>
                        <th>
                            {{ trans('cruds.userSubcrption.fields.status') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($userSubcrptions as $key => $userSubcrption)
                        <tr data-entry-id="{{ $userSubcrption->id }}">
                            <td>

                            </td>
                            <td>
                                {{ $userSubcrption->id ?? '' }}
                            </td>
                            <td>
                                {{ $userSubcrption->selected_days ?? '' }}
                            </td>
                            <td>
                                {{ $userSubcrption->start_date ?? '' }}
                            </td>
                            <td>
                                {{ $userSubcrption->end_date ?? '' }}
                            </td>
                            <td>
                                {{ $userSubcrption->user->name ?? '' }}
                            </td>
                            <td>
                                {{ $userSubcrption->subcrption_plans->title ?? '' }}
                            </td>
                            <td>
                                {{ $userSubcrption->duration->title ?? '' }}
                            </td>
                            <td>
                                {{ $userSubcrption->price ?? '' }}
                            </td>
                            <td>
                                {{ App\Models\UserSubcrption::PAYMENT_SELECT[$userSubcrption->payment] ?? '' }}
                            </td>
                            <td>
                                {{ App\Models\UserSubcrption::STATUS_SELECT[$userSubcrption->status] ?? '' }}
                            </td>
                            <td>
                                @can('user_subcrption_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.user-subcrptions.show', $userSubcrption->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @can('user_subcrption_edit')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.user-subcrptions.edit', $userSubcrption->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcan

                                @can('user_subcrption_delete')
                                    <form action="{{ route('admin.user-subcrptions.destroy', $userSubcrption->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
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
@can('user_subcrption_delete')
  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('admin.user-subcrptions.massDestroy') }}",
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
  let table = $('.datatable-UserSubcrption:not(.ajaxTable)').DataTable({ buttons: dtButtons })
  $('a[data-toggle="tab"]').on('shown.bs.tab click', function(e){
      $($.fn.dataTable.tables(true)).DataTable()
          .columns.adjust();
  });
  
})

</script>
@endsection