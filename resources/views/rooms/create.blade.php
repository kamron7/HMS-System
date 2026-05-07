@extends('layouts.app')

@section('title', 'Добавить номер')

@section('content')
<div class="max-w-2xl mx-auto">

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('rooms.index') }}"
           class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-700 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
            Номера
        </a>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-slate-300"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
        <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">Добавить номер</h1>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-6">
        <form method="POST" action="{{ route('rooms.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="mb-5">
                <label for="number" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    Номер комнаты <span class="text-red-500">*</span>
                </label>
                <input type="text" id="number" name="number" value="{{ old('number') }}" maxlength="10"
                       placeholder="Напр: 101, 202А"
                       class="w-full px-3 py-2.5 border border-slate-200 dark:border-slate-600 rounded-lg text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('number') border-red-400 @enderror">
                @error('number')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="mb-5">
                <label for="floor" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Этаж</label>
                <input type="number" id="floor" name="floor" value="{{ old('floor') }}" min="1" max="100"
                       placeholder="1"
                       class="w-32 px-3 py-2.5 border border-slate-200 dark:border-slate-600 rounded-lg text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('floor') border-red-400 @enderror">
                @error('floor')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="mb-5">
                <label for="room_type_id" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    Тип номера <span class="text-red-500">*</span>
                </label>
                <select id="room_type_id" name="room_type_id"
                        class="w-full px-3 py-2.5 border border-slate-200 dark:border-slate-600 rounded-lg text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('room_type_id') border-red-400 @enderror">
                    <option value="">— Выберите тип —</option>
                    @foreach($roomTypes as $roomType)
                        <option value="{{ $roomType->id }}" @selected(old('room_type_id') == $roomType->id)>
                            {{ $roomType->name }}
                        </option>
                    @endforeach
                </select>
                @error('room_type_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="mb-5">
                <label for="status" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    Статус <span class="text-red-500">*</span>
                </label>
                <select id="status" name="status"
                        class="w-full px-3 py-2.5 border border-slate-200 dark:border-slate-600 rounded-lg text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('status') border-red-400 @enderror">
                    <option value="">— Выберите статус —</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->value }}" @selected(old('status') === $status->value)>
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>
                @error('status')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="mb-6">
                <label for="notes" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Заметки</label>
                <textarea id="notes" name="notes" rows="3" maxlength="1000" placeholder="Необязательно"
                          class="w-full px-3 py-2.5 border border-slate-200 dark:border-slate-600 rounded-lg text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none @error('notes') border-red-400 @enderror">{{ old('notes') }}</textarea>
                @error('notes')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            {{-- Image upload --}}
            <div class="mb-6" x-data="imageUploader(0, 10)">
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
                        Фотографии номера
                        <span class="text-red-500">*</span>
                        <span class="text-xs font-normal text-slate-400 ml-1">(мин. 3, макс. 10)</span>
                    </label>
                    <span class="text-xs font-semibold" :class="count < 3 ? 'text-amber-500' : 'text-emerald-600'" x-text="count + ' / 10'"></span>
                </div>

                {{-- Drop zone --}}
                <div
                    class="relative border-2 border-dashed rounded-xl p-6 text-center cursor-pointer transition-colors"
                    :class="dragging ? 'border-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'border-slate-200 dark:border-slate-600 hover:border-slate-400'"
                    @dragover.prevent="dragging = true"
                    @dragleave.prevent="dragging = false"
                    @drop.prevent="onDrop($event)"
                    @click="$refs.imgInput.click()">
                    <input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple class="hidden"
                           x-ref="imgInput" @change="onFiles($event.target.files)">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                         class="w-8 h-8 mx-auto text-slate-300 dark:text-slate-500 mb-2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9H6"/>
                    </svg>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Перетащите фото сюда или <span class="text-blue-600 font-medium">нажмите для выбора</span></p>
                    <p class="text-xs text-slate-400 mt-0.5">JPG, PNG, WebP · до 4 МБ каждое</p>
                </div>

                {{-- Previews --}}
                <div class="mt-3 grid grid-cols-5 gap-2" x-show="previews.length > 0">
                    <template x-for="(src, i) in previews" :key="i">
                        <div class="relative group aspect-square rounded-lg overflow-hidden border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700">
                            <img :src="src" class="w-full h-full object-cover">
                            <button type="button" @click="removeNew(i)"
                                    class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 text-white">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>

                @error('images')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                @error('images.*')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" :disabled="count < 3"
                        :class="count < 3 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-blue-700'"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Создать
                </button>
                <a href="{{ route('rooms.index') }}"
                   class="px-5 py-2.5 text-sm font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                    Отмена
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function imageUploader(existingCount, max) {
    return {
        dragging: false,
        files: [],
        previews: [],
        get count() { return existingCount + this.files.length; },
        onFiles(fileList) {
            const allowed = max - existingCount - this.files.length;
            const toAdd = Array.from(fileList).slice(0, allowed);
            toAdd.forEach(f => {
                this.files.push(f);
                const r = new FileReader();
                r.onload = e => this.previews.push(e.target.result);
                r.readAsDataURL(f);
            });
            this.syncInput();
        },
        onDrop(e) {
            this.dragging = false;
            this.onFiles(e.dataTransfer.files);
        },
        removeNew(i) {
            this.files.splice(i, 1);
            this.previews.splice(i, 1);
            this.syncInput();
        },
        syncInput() {
            const dt = new DataTransfer();
            this.files.forEach(f => dt.items.add(f));
            this.$refs.imgInput.files = dt.files;
        },
    };
}
</script>
@endsection
