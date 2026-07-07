@if($sortBy === $col)
<span class="um-caret"><x-i :name="$sortDir === 'asc' ? 'chevron-up' : 'chevron-down'" :size="12" /></span>
@else
<span class="um-caret um-caret-dim"><x-i name="chevrons-up-down" :size="12" /></span>
@endif
