<script setup>
const model = defineModel();

defineProps({
	label: String,
	name: String,
	error: String,
	placeholder: {
		type: String,
		default: '',
	},
	type: {
		type: String,
		default: 'text',
	},
});
</script>

<template>
	<div class="relative">
			<!-- <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
				<span class="text-gray-500 sm:text-sm">
					<i :class="`fa-solid fa-${icon}`"></i>
				</span>
			</div> -->
			<input
  :type="type"
  :name="name"
  :placeholder="placeholder"
  v-model="model"
  class="peer p-4 block w-full bg-zinc-200 border-gray-500 border rounded-lg sm:text-sm 
    placeholder:text-transparent focus:outline-none focus:ring-2 focus:ring-blue-500 
    focus:border-gray-500 focus:bg-white 
    disabled:opacity-50 disabled:pointer-events-none
    focus:pt-6 focus:pb-2 
    not-placeholder-shown:pt-6 not-placeholder-shown:pb-2"
/>

	<label  class="absolute top-0 start-0 m-4 sm:text-sm truncate pointer-events-none transition ease-in-out duration-100 border border-transparent  origin-[0_0] peer-disabled:opacity-50 peer-disabled:pointer-events-none
			  peer-focus:scale-100
			  peer-focus:translate-x-1
			  peer-focus:-translate-y-7
			  peer-focus:bg-white
			  peer-focus:px-1
			  peer-focus:text-blue-500
			  peer-placeholder-shown:text-gray-500
			  peer-not-placeholder-shown:scale-100
			  peer-not-placeholder-shown:translate-x-0.1
			  peer-not-placeholder-shown:-translate-y-3
			  peer-not-placeholder-shown:text-gray-500">
			{{ label }}
		</label>
	</div>
	<p v-if="error" class="text-sm text-red-500 my-1 absolute">{{ error }}</p>
</template>