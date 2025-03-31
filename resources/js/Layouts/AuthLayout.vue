<script setup>
defineProps({
    title: String, // Page title (Sign In, Forgot Password, etc.)
});
</script>

<template>
	<Head :title="title" />
	<div class="flex flex-col md:flex-row min-h-screen overflow-y-auto">
		<!-- Left Panel (Remains Constant) -->
		<div class="hidden md:flex w-1/2 bg-blue-900 text-white items-center justify-center p-8">
			<div class="absolute inset-0 w-1/2 bg-black opacity-45"></div>
			<div class="text-center relative">
				<h1 class="text-4xl font-bold mb-2">WELCOME TO CIT PORTAL</h1>
				<p class="text-slate-400">Commitment to Excellence to Serve God and the Community</p>
			</div>
		</div>

		<!-- Right Panel (Dynamic Content) -->
		<div class="md:w-1/2 w-full flex items-center justify-center min-h-screen p-8">
			<slot /> <!-- Dynamic Content Goes Here -->
		</div>
	</div>
</template>
