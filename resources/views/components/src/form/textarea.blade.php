 @props(['name', 'label', 'value' => false, 'required' => false, 'note' => false])

 <div class="relative z-0 flex-auto w-full max-w-full group">
     <textarea name="{{ $name }}" id="{{ $name }}"
         @if ($value) value="{{ $value }}" @endif
         {{ $attributes->merge(['class' => 'block py-2.5 px-0 w-full text-sm text-heading bg-transparent border-0 border-b-2 border-default-medium appearance-none focus:outline-none focus:ring-0 focus:border-brand peer']) }}
         placeholder=" "></textarea>
     <label for="{{ $name }}"
         class="absolute text-sm text-body duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-left peer-focus:start-0 peer-focus:text-fg-brand peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto peer-focus:[&_.required-asterisk]:text-[9px]">
         {!! $label !!}
         @if ($required)
             <div class="required-asterisk text-red-600 opacity-70 text-[9px] relative -top-1.5 inline-block">
                 &#10033;
             </div>
         @endif
     </label>

     @if ($note)
         {!! $note !!}
     @endif

     @error($name)
         <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>
     @enderror

 </div>
