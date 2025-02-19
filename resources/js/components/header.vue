<template>
      <header>
            <button id="menu-btn" @click.stop="toggleSidebar">☰</button>
            <h1 id="chat-title">{{ chatTitle }}</h1>
        </header>
</template>


<script setup>
defineProps({
    chatTitle: String
});
</script>