<script setup>
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faAngleLeft } from '@fortawesome/free-solid-svg-icons'

library.add(faAngleLeft)

defineProps({
  links: Array,
  componentName: String,
});

</script>

<template>
  <nav class="sidenav fixed top-0 border-0 left-0 bg-[#1a3047] w-[20%] h-screen ps-3">
    <div class="flex justify-between items-center">
      <div class="flex items-center gap-x-1">
        <img src="../../../public/assets/img/cit_logo.png" alt="" class="w-[30%] object-cover">
        <h1 class="font-bold text-2xl">CIT PORTAL</h1>
      </div>

      <!-- <div>
            <button class="text-xl text-[#1a3047] bg-[#ffff] rounded-md pt-2 pb-2 pl-3 pr-3 flex justify-center items-center cursor-pointer"><font-awesome-icon icon="angle-left" /></button>
        </div> -->
    </div>
    <ul class="mt-4 flex flex-col gap-y-3">
      <li v-for="link in links" :key="link.name" class="flex flex-col gap-y-2">
        <Link :href="link.url" class="font-semibold p-5 hover:bg-white hover:text-slate-700 hover:rounded-s-full"
          :class="{
            'bg-white text-slate-700 rounded-s-full': $page.component === componentName
          }">
        {{ link.name }}
        </Link>
      </li>
    </ul>
  </nav>
</template>