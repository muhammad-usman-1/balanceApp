@extends('layouts.admin')
@section('content')
@can('meal_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route('admin.meals.create') }}">
                {{ trans('global.add') }} {{ trans('cruds.meal.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('cruds.meal.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable datatable-Meal">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('cruds.meal.fields.id') }}
                        </th>
                        <th>
                            {{ trans('cruds.meal.fields.title') }}
                        </th>
                        <th>
                            {{ trans('cruds.meal.fields.description') }}
                        </th>
                        <th>
                            {{ trans('cruds.meal.fields.image') }}
                        </th>
                        <th>
                            {{ trans('cruds.meal.fields.category') }}
                        </th>
                        <th>
                            {{ trans('cruds.meal.fields.calories') }}
                        </th>
                        <th>
                            {{ trans('cruds.meal.fields.protein_g') }}
                        </th>
                        <th>
                            {{ trans('cruds.meal.fields.fat_g') }}
                        </th>
                        <th>
                            {{ trans('cruds.meal.fields.carbs_g') }}
                        </th>
                        <th>
                            {{ trans('cruds.meal.fields.extras') }}
                        </th>
                        <th>
                            {{ trans('cruds.meal.fields.is_active') }}
                        </th>
                        <th>
                            {{ trans('cruds.meal.fields.type') }}
                        </th>
                        <th>
                            {{ trans('cruds.meal.fields.created_at') }}
                        </th>
                        <th>
                            {{ trans('cruds.meal.fields.updated_at') }}
                        </th>
                        <th>
                            {{ trans('cruds.meal.fields.deleted_at') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($meals as $key => $meal)
                        <tr data-entry-id="{{ $meal->id }}">
                            <td>

                            </td>
                            <td>
                                {{ $meal->id ?? '' }}
                            </td>
                            <td>
                                {{ $meal->title ?? '' }}
                            </td>
                            <td>
                                {{ $meal->description ?? '' }}
                            </td>
                            <td>
                                @php
                                    $image = $meal->getFirstMedia('image');
                                @endphp
                                @if($image)
                                    @php
                                        // Use Spatie's getUrl method which handles custom disks correctly
                                        $imageUrl = $image->getUrl();
                                        $thumbUrl = $image->getUrl('thumb');
                                        
                                        // If thumb doesn't exist, use original
                                        if (!$thumbUrl || $thumbUrl === $imageUrl) {
                                            $thumbUrl = $imageUrl;
                                        }
                                    @endphp
                                    @if($imageUrl)
                                        <img src="{{ $thumbUrl }}" alt="{{ $meal->title ?? '' }}" style="max-width: 50px; max-height: 50px; border: 1px solid #ddd; display: block; object-fit: cover; border-radius: 4px;" onerror="this.onerror=null; this.src='{{ $imageUrl }}';">
                                    @else
                                        <span class="text-muted">No image</span>
                                    @endif
                                @else
                                    <span class="text-muted">No image</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    // Ensure category relationship is loaded
                                    if ($meal->category_id) {
                                        if (!$meal->relationLoaded('category')) {
                                            $meal->load('category');
                                        }
                                        $category = $meal->category;
                                    } else {
                                        $category = null;
                                    }
                                @endphp
                                @if($category && $category->name)
                                    {{ $category->name }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                {{ $meal->calories ?? '' }}
                            </td>
                            <td>
                                {{ $meal->protein_g ?? '' }}
                            </td>
                            <td>
                                {{ $meal->fat_g ?? '' }}
                            </td>
                            <td>
                                {{ $meal->carbs_g ?? '' }}
                            </td>
                            <td>
                                {{ $meal->extras ?? '' }}
                            </td>
                            <td>
                                {{ $meal->is_active ?? '' }}
                            </td>
                            <td>
                                {{ App\Models\Meal::TYPE_RADIO[$meal->type] ?? '' }}
                            </td>
                            <td>
                                {{ $meal->created_at ?? '' }}
                            </td>
                            <td>
                                {{ $meal->updated_at ?? '' }}
                            </td>
                            <td>
                                {{ $meal->deleted_at ?? '' }}
                            </td>
                            <td>
                                @can('meal_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.meals.show', $meal->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @can('meal_edit')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.meals.edit', $meal->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcan

                                @can('meal_delete')
                                    <form action="{{ route('admin.meals.destroy', $meal->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
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
@can('meal_delete')
  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('admin.meals.massDestroy') }}",
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
  let table = $('.datatable-Meal:not(.ajaxTable)').DataTable({ buttons: dtButtons })
  $('a[data-toggle="tab"]').on('shown.bs.tab click', function(e){
      $($.fn.dataTable.tables(true)).DataTable()
          .columns.adjust();
  });

})

</script>
@endsection
