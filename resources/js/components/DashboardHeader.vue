<script setup>
import LogoutBtn from "../components/LogoutBtn.vue"

defineProps({
  content: Array // Accepts an array of links
});
</script>

<template>
    <header class="flex justify-between items-center">
        <div>
            <h1 class="text-xl font-bold text-[#1a3047]">{{ content[0].title }}</h1>
        </div>

        <div class="flex gap-x-5 items-center justify-center">
            <div class="flex gap-x-2 items-center justify-center">
                <div class="flex gap-x-2 items-center justify-end">
                    <img src="https://picsum.photos/id/1/200/300" alt="" class="rounded-full w-10 h-10 object-cover">
                    <button class=" text-[#1a3047] cursor-pointer font-semibold">{{ content[0].name }}</button>
                </div>  
                <p class=" text-[gray] border-l-2 border-gray-500 pl-2.5">{{ content[0].userType }}</p>
            </div>

            <div>
                <LogoutBtn />
             </div>
        </div>
    </header>
</template>