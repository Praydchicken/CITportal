<template>
    <main class="mainLogin-custom">
        <header class="header-custom flex items-center gap-x-8 p-2">
            <div class="w-auto h-auto flex justify-items-end items-end">
                <img src="../../../public/assets/img/cit_logo.png" alt="" class="object-fit h-20 w-20">
            </div>
            <div>
                <h1 class="text-2xl font-bold">CIT Student Portal</h1>
            </div>
        </header>

        <section>
            <slot />
        </section>
    </main>
</template>