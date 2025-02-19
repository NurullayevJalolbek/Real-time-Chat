<template>
    <aside class="sidebar" :class="{ open: sidebarVisible }">
        <h2>Chats</h2>
        <button v-for="chat in sidebarChats" :key="chat.id" class="chat-button" @click="loadChat(chat.name, chat.id)">
            {{ chat.name }}
        </button>
    </aside>
</template>

<script setup>
defineProps({
    sidebarVisible: Boolean,
    sidebarChats: Array
});

const emit = defineEmits(["chat-selected"]);

const loadChat = (name, id) => {
    emit("chat-selected", name, id);
};
</script>
