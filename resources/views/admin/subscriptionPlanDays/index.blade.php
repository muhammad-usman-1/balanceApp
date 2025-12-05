@extends('layouts.admin')
@section('content')
@can('subscription_plan_day_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route('admin.subscription-plan-days.create') }}">
                {{ trans('global.add') }} {{ trans('cruds.subscriptionPlanDay.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('cruds.subscriptionPlanDay.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable datatable-SubscriptionPlanDay">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('cruds.subscriptionPlanDay.fields.id') }}
                        </th>
                        <th>
                            {{ trans('cruds.subscriptionPlanDay.fields.user_subcrption') }}
                        </th>
                        <th>
                            {{ trans('cruds.subscriptionPlanDay.fields.subscription_plans') }}
                        </th>
                        <th>
                            {{ trans('cruds.subscriptionPlanDay.fields.day') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($subscriptionDays as $key => $subscriptionDay)
                        <tr data-entry-id="{{ $subscriptionDay->id }}">
                            <td>

                            </td>
                            <td>
                                {{ $subscriptionDay->id ?? '' }}
                            </td>
                            <td>
                                User Subscription #{{ $subscriptionDay->user_subcrptions_id ?? '' }}
                                @if($subscriptionDay->user_subcrption)
                                    <br><small class="text-muted">{{ $subscriptionDay->user_subcrption->user->name ?? 'N/A' }}</small>
                                @endif
                            </td>
                            <td>
                                {{ $subscriptionDay->user_subcrption->subcrption_plans->title ?? 'N/A' }}
                            </td>
                            <td>
                                {{ App\Models\SubscriptionDay::DAY_SELECT[$subscriptionDay->day] ?? $subscriptionDay->day }}
                            </td>
                            <td>
                                @can('subscription_plan_day_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.subscription-plan-days.show', $subscriptionDay->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @can('subscription_plan_day_edit')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.subscription-plan-days.edit', $subscriptionDay->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcan

                                @can('subscription_plan_day_delete')
                                    <form action="{{ route('admin.subscription-plan-days.destroy', $subscriptionDay->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
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
@can('subscription_plan_day_delete')
  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('admin.subscription-plan-days.massDestroy') }}",
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
  let table = $('.datatable-SubscriptionPlanDay:not(.ajaxTable)').DataTable({ buttons: dtButtons })
  $('a[data-toggle="tab"]').on('shown.bs.tab click', function(e){
      $($.fn.dataTable.tables(true)).DataTable()
          .columns.adjust();
  });
  
})

</script>
@endsection