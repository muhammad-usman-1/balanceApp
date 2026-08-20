{{-- Available Extras selector — expects $mealExtras, $selectedExtraIds, $selectedIngredientIds --}}
<style>
.me-extras-wrap { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 14px; }
.me-extra-card { border: 1px solid #e5e7eb; border-radius: 12px; padding: 12px 14px; background: #fafafa;
                 transition: border-color .15s, background .15s; }
.me-extra-card.is-on { border-color: #99f6e4; background: #f0fdfa; }
.me-extra-head { display: flex; align-items: center; gap: 9px; cursor: pointer; margin: 0; }
.me-extra-head input { width: 16px; height: 16px; accent-color: #0d9488; }
.me-extra-name { font-size: .9rem; font-weight: 700; color: #111827; }
.me-extra-meta { margin-left: auto; display: flex; gap: 5px; }
.me-tag { font-size: .64rem; font-weight: 700; padding: 2px 7px; border-radius: 20px; text-transform: uppercase; letter-spacing: .03em; }
.me-tag-single { background: #eef2ff; color: #4338ca; }
.me-tag-multiple { background: #ecfeff; color: #0e7490; }
.me-tag-req { background: #fee2e2; color: #dc2626; }
.me-ing-grid { display: flex; flex-direction: column; gap: 6px; margin-top: 10px; padding-top: 10px; border-top: 1px dashed #e5e7eb; }
.me-ing { display: flex; align-items: center; gap: 8px; font-size: .82rem; color: #374151; cursor: pointer; margin: 0; }
.me-ing input { width: 15px; height: 15px; accent-color: #0d9488; }
.me-ing input:disabled { cursor: not-allowed; }
.me-ing.is-off { color: #9ca3af; }
.me-no-ing { font-size: .76rem; color: #9ca3af; margin-top: 8px; font-style: italic; }
.me-empty { font-size: .84rem; color: #6b7280; padding: 14px; border: 1px dashed #cbd5e1; border-radius: 10px; background: #f8fafc; }
.me-max { display: flex; align-items: center; gap: 8px; margin-top: 10px; padding-top: 10px; border-top: 1px dashed #e5e7eb; }
.me-max-label { font-size: .74rem; font-weight: 700; color: #374151; }
.me-max-input { width: 62px; padding: 5px 8px; font-size: .82rem; border: 1px solid #e5e7eb; border-radius: 7px; outline: none; }
.me-max-input:focus { border-color: #0d9488; box-shadow: 0 0 0 3px rgba(13,148,136,.1); }
.me-max-hint { font-size: .72rem; color: #9ca3af; }
</style>

<p class="mf-section" style="margin-top:10px;"><i class="fas fa-plus-square mr-1"></i> Available Extras</p>

@if($mealExtras->isEmpty())
    <div class="me-empty">
        <i class="fas fa-info-circle"></i> No extras defined yet.
        <a href="{{ route('admin.meal-extras.index') }}" target="_blank">Create meal extras</a> first, then attach them here.
    </div>
@else
    <p style="font-size:.78rem;color:#9ca3af;margin:10px 10px 12px;">
        Tick the extras this meal offers, then choose which options are available under each.
    </p>
    <div class="me-extras-wrap">
        @foreach($mealExtras as $extra)
            @php $extraChecked = in_array($extra->id, $selectedExtraIds); @endphp
            <div class="me-extra-card {{ $extraChecked ? 'is-on' : '' }}" data-extra-card="{{ $extra->id }}">
                <label class="me-extra-head">
                    <input type="checkbox" name="meal_extras[]" value="{{ $extra->id }}"
                           class="me-extra-toggle" {{ $extraChecked ? 'checked' : '' }}>
                    <span class="me-extra-name">{{ $extra->name }}</span>
                    <span class="me-extra-meta">
                        <span class="me-tag {{ $extra->selection_type === 'multiple' ? 'me-tag-multiple' : 'me-tag-single' }}">
                            {{ $extra->selection_type === 'multiple' ? 'Multi' : 'Single' }}
                        </span>
                        @if($extra->is_required)<span class="me-tag me-tag-req">Req</span>@endif
                    </span>
                </label>

                @if($extra->activeIngredients->isEmpty())
                    <div class="me-no-ing">No options in this extra yet.</div>
                @else
                    <div class="me-ing-grid">
                        @foreach($extra->activeIngredients as $ing)
                            <label class="me-ing {{ $extraChecked ? '' : 'is-off' }}">
                                <input type="checkbox" name="extra_ingredients[]" value="{{ $ing->id }}"
                                       {{ in_array($ing->id, $selectedIngredientIds) ? 'checked' : '' }}
                                       {{ $extraChecked ? '' : 'disabled' }}>
                                <span>{{ $ing->name }}</span>
                            </label>
                        @endforeach
                    </div>

                    @if($extra->selection_type === 'multiple')
                        <div class="me-max">
                            <span class="me-max-label">Max user can pick</span>
                            <input type="number" class="me-max-input" min="1"
                                   name="max_select[{{ $extra->id }}]"
                                   value="{{ ($selectedMaxSelect ?? [])[$extra->id] ?? '' }}"
                                   placeholder="Any"
                                   {{ $extraChecked ? '' : 'disabled' }}>
                            <span class="me-max-hint">of {{ $extra->activeIngredients->count() }} options · blank = no limit</span>
                        </div>
                    @endif
                @endif
            </div>
        @endforeach
    </div>

    <script>
    (function () {
        document.querySelectorAll('[data-extra-card]').forEach(function (card) {
            var toggle = card.querySelector('.me-extra-toggle');
            if (!toggle) return;
            toggle.addEventListener('change', function () {
                var on = this.checked;
                card.classList.toggle('is-on', on);
                card.querySelectorAll('.me-ing').forEach(function (lbl) {
                    var cb = lbl.querySelector('input');
                    cb.disabled = !on;
                    if (!on) cb.checked = false;
                    lbl.classList.toggle('is-off', !on);
                });
                var maxInput = card.querySelector('.me-max-input');
                if (maxInput) maxInput.disabled = !on;
            });
        });
    })();
    </script>
@endif
