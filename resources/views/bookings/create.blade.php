@extends('layouts.app')

@section('title', 'Новое бронирование')

@section('content')
<div class="max-w-4xl mx-auto">

    {{-- Page header --}}
    <div class="mb-6 flex items-center gap-4">
        <a href="{{ route('bookings.index') }}"
           class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1">
            ← Бронирования
        </a>
        <h1 class="text-2xl font-bold text-gray-900">Новое бронирование</h1>
    </div>

    {{-- Validation errors (server-side fallback) --}}
    @if($errors->any())
        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Alpine.js Wizard --}}
    <form method="POST" action="{{ route('bookings.store') }}"
          x-data="{
              step: 1,

              // Step 1 — Dates
              checkIn: '{{ old('check_in_date', '') }}',
              checkOut: '{{ old('check_out_date', '') }}',
              nights: 0,

              // Step 2 — Room
              rooms: [],
              selectedRoom: null,
              loadingRooms: false,

              // Step 3 — Guest
              guestQuery: '',
              guestResults: [],
              selectedGuest: null,
              showGuestDropdown: false,
              adults: {{ old('adults', 1) }},
              children: {{ old('children', 0) }},
              notes: '{{ old('notes', '') }}',

              get totalPrice() {
                  if (!this.selectedRoom || this.nights <= 0) return 0;
                  return this.nights * this.selectedRoom.room_type.base_price;
              },

              get totalPriceFormatted() {
                  if (!this.totalPrice) return '—';
                  return new Intl.NumberFormat('ru-RU').format(this.totalPrice) + ' сум';
              },

              init() {
                  // Recompute nights on init if dates are pre-filled (old input)
                  this.computeNights();
              },

              computeNights() {
                  if (this.checkIn && this.checkOut) {
                      const d1 = new Date(this.checkIn);
                      const d2 = new Date(this.checkOut);
                      this.nights = Math.max(0, Math.round((d2 - d1) / 86400000));
                  } else {
                      this.nights = 0;
                  }
              },

              async loadRooms() {
                  if (!this.checkIn || !this.checkOut || this.nights <= 0) return;
                  this.loadingRooms = true;
                  this.rooms = [];
                  this.selectedRoom = null;
                  const res = await fetch(`/rooms/available?check_in=${this.checkIn}&check_out=${this.checkOut}`);
                  this.rooms = await res.json();
                  this.loadingRooms = false;
              },

              async searchGuests() {
                  if (this.guestQuery.length < 2) {
                      this.guestResults = [];
                      this.showGuestDropdown = false;
                      return;
                  }
                  const res = await fetch(`/guests/search?q=${encodeURIComponent(this.guestQuery)}`);
                  this.guestResults = await res.json();
                  this.showGuestDropdown = this.guestResults.length > 0;
              },

              selectGuest(guest) {
                  this.selectedGuest = guest;
                  this.guestQuery = guest.full_name;
                  this.guestResults = [];
                  this.showGuestDropdown = false;
              },

              canGoToStep2() { return this.nights > 0; },
              canGoToStep3() { return this.selectedRoom !== null; },
              canSubmit() { return this.selectedGuest !== null && this.adults >= 1; },

              nextStep() {
                  if (this.step === 1 && this.canGoToStep2()) {
                      this.loadRooms();
                      this.step = 2;
                  } else if (this.step === 2 && this.canGoToStep3()) {
                      this.step = 3;
                  }
              },
              prevStep() {
                  if (this.step > 1) this.step--;
              }
          }">
        @csrf

        {{-- Hidden inputs (updated reactively) --}}
        <input type="hidden" name="room_id"        :value="selectedRoom ? selectedRoom.id : ''">
        <input type="hidden" name="guest_id"       :value="selectedGuest ? selectedGuest.id : ''">
        <input type="hidden" name="check_in_date"  :value="checkIn">
        <input type="hidden" name="check_out_date" :value="checkOut">
        <input type="hidden" name="adults"         :value="adults">
        <input type="hidden" name="children"       :value="children">
        <input type="hidden" name="notes"          :value="notes">

        {{-- Step indicator --}}
        <div class="mb-8 flex items-center gap-0">
            {{-- Step 1 --}}
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-colors"
                     :class="step > 1 ? 'bg-green-500 text-white' : (step === 1 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-500')">
                    <span x-show="step <= 1">1</span>
                    <span x-show="step > 1">✓</span>
                </div>
                <span class="text-sm font-medium"
                      :class="step === 1 ? 'text-blue-700' : (step > 1 ? 'text-green-700' : 'text-gray-400')">
                    Даты
                </span>
            </div>
            <div class="flex-1 h-px bg-gray-300 mx-3"></div>
            {{-- Step 2 --}}
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-colors"
                     :class="step > 2 ? 'bg-green-500 text-white' : (step === 2 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-500')">
                    <span x-show="step <= 2">2</span>
                    <span x-show="step > 2">✓</span>
                </div>
                <span class="text-sm font-medium"
                      :class="step === 2 ? 'text-blue-700' : (step > 2 ? 'text-green-700' : 'text-gray-400')">
                    Номер
                </span>
            </div>
            <div class="flex-1 h-px bg-gray-300 mx-3"></div>
            {{-- Step 3 --}}
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-colors"
                     :class="step === 3 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-500'">
                    3
                </div>
                <span class="text-sm font-medium"
                      :class="step === 3 ? 'text-blue-700' : 'text-gray-400'">
                    Гость
                </span>
            </div>
        </div>

        {{-- ================================================================== --}}
        {{-- STEP 1: Dates --}}
        {{-- ================================================================== --}}
        <div x-show="step === 1" x-cloak>
            <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900 mb-5">Выберите даты</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Дата заезда <span class="text-red-500">*</span>
                        </label>
                        <input type="date"
                               x-model="checkIn"
                               @change="computeNights()"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Дата выезда <span class="text-red-500">*</span>
                        </label>
                        <input type="date"
                               x-model="checkOut"
                               @change="computeNights()"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div x-show="nights > 0" class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-800">
                    Ночей: <span class="font-bold" x-text="nights"></span>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="button"
                            @click="nextStep()"
                            :disabled="!canGoToStep2()"
                            :class="canGoToStep2() ? 'bg-blue-600 hover:bg-blue-700 text-white' : 'bg-gray-200 text-gray-400 opacity-50 cursor-not-allowed'"
                            class="px-5 py-2 rounded-lg text-sm font-medium transition-colors">
                        Далее →
                    </button>
                </div>
            </div>
        </div>

        {{-- ================================================================== --}}
        {{-- STEP 2: Room selection --}}
        {{-- ================================================================== --}}
        <div x-show="step === 2" x-cloak>
            <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900 mb-5">Выберите номер</h2>

                {{-- Loading spinner --}}
                <div x-show="loadingRooms" class="flex items-center justify-center py-12">
                    <div class="w-8 h-8 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
                    <span class="ml-3 text-sm text-gray-500">Загрузка номеров…</span>
                </div>

                {{-- No rooms available --}}
                <div x-show="!loadingRooms && rooms.length === 0"
                     class="text-center py-12 text-gray-500 text-sm">
                    Нет доступных номеров на выбранные даты
                </div>

                {{-- Room grid --}}
                <div x-show="!loadingRooms && rooms.length > 0"
                     class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <template x-for="room in rooms" :key="room.id">
                        <div @click="selectedRoom = room"
                             :class="selectedRoom && selectedRoom.id === room.id
                                 ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-400'
                                 : 'border-gray-200 hover:border-blue-300 hover:bg-gray-50'"
                             class="border-2 rounded-xl p-4 cursor-pointer transition-all">
                            <div class="flex items-start justify-between mb-2">
                                <div>
                                    <p class="font-bold text-gray-900 text-base" x-text="'Номер ' + room.number"></p>
                                    <p class="text-sm text-gray-500" x-text="room.room_type.name"></p>
                                </div>
                                <div x-show="selectedRoom && selectedRoom.id === room.id"
                                     class="w-5 h-5 bg-blue-500 rounded-full flex items-center justify-center text-white text-xs font-bold">
                                    ✓
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mb-1" x-text="'Этаж: ' + room.floor"></p>
                            <p class="text-xs text-gray-500 mb-3"
                               x-text="'Вместимость: ' + room.room_type.capacity + ' чел.'"></p>
                            <p class="text-sm font-semibold text-blue-700"
                               x-text="new Intl.NumberFormat('ru-RU').format(room.room_type.base_price) + ' сум/ночь'"></p>
                        </div>
                    </template>
                </div>

                <div class="mt-6 flex justify-between">
                    <button type="button"
                            @click="prevStep()"
                            class="px-5 py-2 rounded-lg text-sm font-medium border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors">
                        ← Назад
                    </button>
                    <button type="button"
                            @click="nextStep()"
                            :disabled="!canGoToStep3()"
                            :class="canGoToStep3() ? 'bg-blue-600 hover:bg-blue-700 text-white' : 'bg-gray-200 text-gray-400 opacity-50 cursor-not-allowed'"
                            class="px-5 py-2 rounded-lg text-sm font-medium transition-colors">
                        Далее →
                    </button>
                </div>
            </div>
        </div>

        {{-- ================================================================== --}}
        {{-- STEP 3: Guest & Details --}}
        {{-- ================================================================== --}}
        <div x-show="step === 3" x-cloak>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Left: form fields --}}
                <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-gray-900 mb-5">Данные гостя и детали</h2>

                    {{-- Guest autocomplete --}}
                    <div class="mb-5" @click.away="showGuestDropdown = false">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Гость <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="text"
                                   x-model="guestQuery"
                                   @input.debounce.300ms="searchGuests()"
                                   @focus="if (guestResults.length > 0) showGuestDropdown = true"
                                   placeholder="Начните вводить имя или телефон…"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">

                            {{-- Dropdown --}}
                            <div x-show="showGuestDropdown"
                                 x-cloak
                                 class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-52 overflow-y-auto">
                                <template x-for="guest in guestResults" :key="guest.id">
                                    <div @click="selectGuest(guest)"
                                         class="px-4 py-2.5 hover:bg-blue-50 cursor-pointer text-sm border-b border-gray-100 last:border-0">
                                        <p class="font-medium text-gray-900" x-text="guest.full_name"></p>
                                        <p class="text-xs text-gray-500" x-text="guest.phone"></p>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Selected guest badge --}}
                        <div x-show="selectedGuest" x-cloak class="mt-2 flex items-center gap-2">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">
                                ✓ <span x-text="selectedGuest ? selectedGuest.full_name : ''"></span>
                            </span>
                            <button type="button"
                                    @click="selectedGuest = null; guestQuery = ''"
                                    class="text-xs text-gray-400 hover:text-red-500">
                                Изменить
                            </button>
                        </div>
                    </div>

                    {{-- Adults --}}
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Количество взрослых <span class="text-red-500">*</span>
                        </label>
                        <input type="number"
                               x-model.number="adults"
                               min="1" max="20"
                               class="w-32 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    {{-- Children --}}
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Количество детей
                        </label>
                        <input type="number"
                               x-model.number="children"
                               min="0" max="20"
                               class="w-32 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    {{-- Notes --}}
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Примечания
                        </label>
                        <textarea x-model="notes"
                                  rows="3"
                                  maxlength="1000"
                                  placeholder="Особые пожелания, аллергии и т.д."
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                    </div>

                    <div class="flex justify-between">
                        <button type="button"
                                @click="prevStep()"
                                class="px-5 py-2 rounded-lg text-sm font-medium border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors">
                            ← Назад
                        </button>
                        <button type="submit"
                                :disabled="!canSubmit()"
                                :class="canSubmit() ? 'bg-blue-600 hover:bg-blue-700 text-white' : 'bg-gray-200 text-gray-400 opacity-50 cursor-not-allowed'"
                                class="px-6 py-2 rounded-lg text-sm font-medium transition-colors">
                            Создать бронирование
                        </button>
                    </div>
                </div>

                {{-- Right: Summary --}}
                <div class="lg:col-span-1">
                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-5 sticky top-6">
                        <h3 class="text-sm font-semibold text-blue-900 mb-3">Итог бронирования</h3>
                        <div class="space-y-2 text-sm text-blue-800">
                            <div class="flex justify-between">
                                <span class="text-blue-600">Номер:</span>
                                <span class="font-medium" x-text="selectedRoom ? ('№' + selectedRoom.number + ' — ' + selectedRoom.room_type.name) : '—'"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-blue-600">Заезд:</span>
                                <span class="font-medium" x-text="checkIn || '—'"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-blue-600">Выезд:</span>
                                <span class="font-medium" x-text="checkOut || '—'"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-blue-600">Ночей:</span>
                                <span class="font-medium" x-text="nights > 0 ? nights : '—'"></span>
                            </div>
                            <div class="flex justify-between border-t border-blue-200 pt-2 mt-2">
                                <span class="font-semibold text-blue-900">Итого:</span>
                                <span class="font-bold text-blue-900" x-text="totalPriceFormatted"></span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </form>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>
@endsection
