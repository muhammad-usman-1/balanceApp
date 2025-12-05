@extends('layouts.admin')
@section('content')
{{--  @can('user_subcrption_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route('admin.user-subcrptions.create') }}">
                {{ trans('global.add') }} {{ trans('cruds.userSubcrption.title_singular') }}
            </a>
        </div>
    </div>
@endcan  --}}
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
                            {{ trans('cruds.userSubcrption.fields.user') }}
                        </th>
                        <th>
                            {{ trans('cruds.userSubcrption.fields.subcrption_plans') }}
                        </th>


                        <th>
                            {{ trans('cruds.userSubcrption.fields.payment') }}
                        </th>
                        <th>
                            {{ trans('cruds.userSubcrption.fields.status') }}
                        </th>
                        <th>
                            {{ trans('cruds.userSubcrption.fields.is_personalized') }}
                        </th>
                        <th>
                            {{ trans('cruds.userSubcrption.fields.protein') }}
                        </th>
                        <th>
                            {{ trans('cruds.userSubcrption.fields.carbs') }}
                        </th>
                        <th>
                             Actions
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
                                {{ $userSubcrption->user->name ?? '' }}
                            </td>
                            <td>
                                {{ $userSubcrption->subcrption_plans->title ?? '' }}
                            </td>


                            <td>
                                {{ App\Models\UserSubcrption::PAYMENT_SELECT[$userSubcrption->payment] ?? '' }}
                            </td>
                            <td>
                                {{ App\Models\UserSubcrption::STATUS_SELECT[$userSubcrption->status] ?? '' }}
                            </td>
                            <td>
                                @if($userSubcrption->is_personalized)
                                    <span class="badge badge-success">Yes</span>
                                @else
                                    <span class="badge badge-secondary">No</span>
                                @endif
                            </td>
                            <td>
                                {{ $userSubcrption->protein ?? '-' }}
                            </td>
                            <td>
                                {{ $userSubcrption->carbs ?? '-' }}
                            </td>
                            <td>
                                @can('user_subcrption_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.user-subcrptions.show', $userSubcrption->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                <button type="button" class="btn btn-xs btn-success view-subscription-details" data-subscription-id="{{ $userSubcrption->id }}" data-toggle="modal" data-target="#subscriptionDetailsModal">
                                    <i class="fas fa-utensils"></i> View Meals
                                </button>



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

<!-- Subscription Details Modal -->
<div class="modal fade" id="subscriptionDetailsModal" tabindex="-1" role="dialog" aria-labelledby="subscriptionDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document" style="max-width: 900px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="subscriptionDetailsModalLabel">Subscription Details & Meals</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="subscriptionDetailsContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.view-subscription-details').on('click', function() {
        var subscriptionId = $(this).data('subscription-id');
        var modal = $('#subscriptionDetailsModal');
        var content = $('#subscriptionDetailsContent');

        // Show loading
        content.html('<div class="text-center"><div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div></div>');

        // Fetch subscription details
        $.ajax({
            url: '{{ route("admin.user-subcrptions.details", ":id") }}'.replace(':id', subscriptionId),
            method: 'GET',
            success: function(response) {
                content.html(response);
            },
            error: function(xhr) {
                content.html('<div class="alert alert-danger">Error loading subscription details. Please try again.</div>');
            }
        });
    });
});
</script>
@endsection
