<template x-for="field in timeFields" :key="field.key">
    <div class="erfan-datepicker__wheel erfan-datepicker__wheel--time" :data-wheel="field.key" @wheel="wheel(field.key, $event)" @touchstart.passive="touchStart($event)" @touchend="touchEnd(field.key, $event)">
        <button type="button" class="erfan-datepicker__arrow" :aria-label="'کاهش ' + field.label" :disabled="draft[field.key] <= limits(field.key)[0]" @click="move(field.key, -1)">⌃</button>
        @if($wheelTime)
            <span class="erfan-datepicker__neighbor" aria-hidden="true" x-text="displayField(field.key, -1)"></span>
        @endif
        <input
            type="text"
            inputmode="numeric"
            autocomplete="off"
            class="erfan-datepicker__selected {{ $ui['input'] }}"
            role="spinbutton"
            :aria-label="field.label"
            :aria-valuemin="limits(field.key)[0]"
            :aria-valuemax="limits(field.key)[1]"
            :aria-valuenow="draft[field.key]"
            :value="displayField(field.key)"
            @focus="$el.select()"
            @blur="setField(field.key, $el.value, $el)"
            @keydown.enter.prevent="setField(field.key, $el.value, $el)"
            @keydown.up.prevent="move(field.key, 1)"
            @keydown.down.prevent="move(field.key, -1)"
            @keydown.page-up.prevent="move(field.key, 5)"
            @keydown.page-down.prevent="move(field.key, -5)"
        >
        @if($wheelTime)
            <span class="erfan-datepicker__neighbor" aria-hidden="true" x-text="displayField(field.key, 1)"></span>
        @endif
        <button type="button" class="erfan-datepicker__arrow" :aria-label="'افزایش ' + field.label" :disabled="draft[field.key] >= limits(field.key)[1]" @click="move(field.key, 1)">⌄</button>
    </div>
</template>
