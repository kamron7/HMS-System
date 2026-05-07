@extends('layouts.app')

@section('title', 'Изменить номер')

@section('content')
<div class="max-w-2xl mx-auto">

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('rooms.index') }}"
           class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-700 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
            Номера
        </a>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-slate-300"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
        <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">Номер {{ $room->number }}</h1>
    </div>

    {{-- QR Code card --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-6 mb-5 flex items-center gap-6">
        <div class="flex-shrink-0">
            <img src="{{ route('room-portal.qr-image', $room->qr_token) }}" alt="QR номер {{ $room->number }}"
                 class="w-24 h-24 rounded-lg border border-slate-200 dark:border-slate-600 bg-white p-1">
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-slate-900 dark:text-slate-100 mb-0.5">QR-код для гостей</p>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">
                Распечатайте и разместите в номере. Гость сканирует и попадает на портал — заказы, отзыв, связь с ресепшн.
            </p>
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('rooms.qr', $room) }}" download="room-{{ $room->number }}-qr.svg"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/30 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    Скачать SVG
                </a>
                <a href="{{ route('room-portal.show', $room->qr_token) }}" target="_blank"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                    Открыть портал
                </a>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-6">
        <form method="POST" action="{{ route('rooms.update', $room) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-5">
                <label for="number" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    Номер комнаты <span class="text-red-500">*</span>
                </label>
                <input type="text" id="number" name="number" value="{{ old('number', $room->number) }}" maxlength="10"
                       placeholder="Напр: 101, 202А"
                       class="w-full px-3 py-2.5 border border-slate-200 dark:border-slate-600 rounded-lg text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('number') border-red-400 @enderror">
                @error('number')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="mb-5">
                <label for="floor" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Этаж</label>
                <input type="number" id="floor" name="floor" value="{{ old('floor', $room->floor) }}" min="1" max="100"
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
                        <option value="{{ $roomType->id }}" @selected(old('room_type_id', $room->room_type_id) == $roomType->id)>
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
                        <option value="{{ $status->value }}" @selected(old('status', $room->status->value) === $status->value)>
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>
                @error('status')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="mb-6">
                <label for="notes" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Заметки</label>
                <textarea id="notes" name="notes" rows="3" maxlength="1000" placeholder="Необязательно"
                          class="w-full px-3 py-2.5 border border-slate-200 dark:border-slate-600 rounded-lg text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none @error('notes') border-red-400 @enderror">{{ old('notes', $room->notes) }}</textarea>
                @error('notes')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            {{-- Image management --}}
            @php $existingImages = $room->images ?? []; @endphp
            <div class="mb-6" x-data="imageUploader({{ count($existingImages) }}, 10)">
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
                        Фотографии номера
                        <span class="text-xs font-normal text-slate-400 ml-1">(мин. 3, макс. 10)</span>
                    </label>
                    <span class="text-xs font-semibold" :class="count < 3 ? 'text-amber-500' : 'text-emerald-600'" x-text="count + ' / 10'"></span>
                </div>

                {{-- Existing images --}}
                @if(count($existingImages))
                <div class="grid grid-cols-5 gap-2 mb-3" id="existing-imgs">
                    @foreach($existingImages as $imgPath)
                    <div class="relative group aspect-square rounded-lg overflow-hidden border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700"
                         id="img-{{ md5($imgPath) }}" x-data>
                        <img src="{{ asset('storage/' . $imgPath) }}" class="w-full h-full object-cover" alt="">
                        <button type="button"
                                @click="deleteExisting('{{ $imgPath }}', '{{ md5($imgPath) }}')"
                                class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 text-white">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    @endforeach
                </div>
                @endif

                {{-- Drop zone (hidden when at max) --}}
                <div x-show="count < 10"
                    class="relative border-2 border-dashed rounded-xl p-5 text-center cursor-pointer transition-colors"
                    :class="dragging ? 'border-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'border-slate-200 dark:border-slate-600 hover:border-slate-400'"
                    @dragover.prevent="dragging = true"
                    @dragleave.prevent="dragging = false"
                    @drop.prevent="onDrop($event)"
                    @click="$refs.imgInput.click()">
                    <input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple class="hidden"
                           x-ref="imgInput" @change="onFiles($event.target.files)">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                         class="w-7 h-7 mx-auto text-slate-300 dark:text-slate-500 mb-1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9H6"/>
                    </svg>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Перетащите или <span class="text-blue-600 font-medium">выберите</span> новые фото</p>
                    <p class="text-xs text-slate-400 mt-0.5">JPG, PNG, WebP · до 4 МБ</p>
                </div>

                {{-- New previews --}}
                <div class="mt-2 grid grid-cols-5 gap-2" x-show="previews.length > 0">
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
                <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    Сохранить
                </button>
                <a href="{{ route('rooms.index') }}"
                   class="px-5 py-2.5 text-sm font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                    Отмена
                </a>
            </div>
        </form>
    </div>
    {{-- Reviews for this room --}}
    @if($reviews->isNotEmpty())
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden mt-5">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100">Отзывы о номере</h2>
                <p class="text-xs text-slate-400 mt-0.5">
                    Средняя оценка:
                    <span class="font-semibold text-slate-700 dark:text-slate-300">
                        {{ number_format($reviews->avg('rating'), 1) }}★
                    </span>
                    ({{ $reviews->count() }} отз.)
                </p>
            </div>
        </div>
        <ul class="divide-y divide-slate-100 dark:divide-slate-700">
            @foreach($reviews as $review)
            <li class="px-5 py-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-yellow-400 text-sm">{{ str_repeat('★', $review->rating) }}<span class="text-slate-300 dark:text-slate-600">{{ str_repeat('★', 5 - $review->rating) }}</span></span>
                            <span class="text-xs text-slate-400">{{ $review->submitted_at?->format('d.m.Y H:i') }}</span>
                            @if($review->guest)
                            <a href="{{ route('guests.show', $review->guest) }}" class="text-xs text-blue-600 hover:underline">— {{ $review->guest->fullName }}</a>
                            @endif
                            @if($review->booking)
                            <a href="{{ route('bookings.show', $review->booking) }}" class="text-xs text-slate-400 font-mono hover:text-blue-600">({{ $review->booking->booking_ref }})</a>
                            @endif
                        </div>
                        @if($review->photos)
                        <div class="flex gap-2 mb-2">
                            @foreach(explode(',', $review->photos) as $photo)
                            <a href="{{ asset('storage/' . trim($photo)) }}" target="_blank" class="rounded-lg overflow-hidden border border-slate-200 hover:opacity-90 transition-opacity flex-shrink-0">
                                <img src="{{ asset('storage/' . trim($photo)) }}" class="w-16 h-16 object-cover" alt="">
                            </a>
                            @endforeach
                        </div>
                        @endif
                        @if($review->comment)
                        <p class="text-sm text-slate-700 dark:text-slate-300">{{ $review->comment }}</p>
                        @endif
                    </div>
                    @if(auth()->user()->role->value === 'owner')
                    <form method="POST" action="{{ route('reviews.destroy', $review) }}" onsubmit="return confirm('Удалить отзыв?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-slate-400 hover:text-red-600 transition-colors flex-shrink-0 p-1" title="Удалить отзыв">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                        </button>
                    </form>
                    @endif
                </div>
            </li>
            @endforeach
        </ul>
    </div>
    @endif

</div>

<script>
function imageUploader(existingCount, max) {
    return {
        dragging: false,
        files: [],
        previews: [],
        existing: existingCount,
        get count() { return this.existing + this.files.length; },
        onFiles(fileList) {
            const allowed = max - this.count;
            Array.from(fileList).slice(0, allowed).forEach(f => {
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
        async deleteExisting(path, hash) {
            if (!confirm('Удалить фото?')) return;
            const res = await fetch('{{ route('rooms.images.destroy', $room) }}', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ path }),
            });
            if (res.ok) {
                document.getElementById('img-' + hash)?.remove();
                this.existing = Math.max(0, this.existing - 1);
            }
        },
    };
}
</script>
@endsection
