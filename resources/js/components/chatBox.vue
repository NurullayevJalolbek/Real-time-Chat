<template>
 <div class="chat-box" ref="chatBox">
            <div v-for="(message, index) in messages" :key="index"
                :class="['message', message.sender_id === currentChatId ? 'left' : 'right']">

                <!-- Vaqtni chiqarish -->
                <span v-if="message.sender_id === currentChatId" class="time" style="color:black">
                    {{ formattedTime(message.created_at) }}
                </span>

                <!-- Matnli xabar -->
                <span v-if="message.text !== null">
                    {{ message.text }}
                </span>

                <div v-else-if="message.file_path !== null">
                    <!-- Agar rasm bo‘lsa -->
                    <img v-if="message.file_type.startsWith('image/')" :src="'/storage/Media/' + message.file_path"
                        alt="Sent image" style="max-width: 200px; border-radius: 10px;"
                        @click="openImageModal('/storage/Media/' + message.file_path)">
                    <!-- Agar rasm bo‘lmasa (PDF, Word, yoki boshqa fayl bo‘lsa) -->
                    <a v-else :href="'/' + message.file_path" target="_blank"
                        style="color: blue; text-decoration: underline;">
                        Faylni yuklash
                    </a>
                </div>

                <!-- Vaqtni chiqarish -->
                <span v-if="message.sender_id !== currentChatId" class="time" style="color:black">
                    {{ formattedTime(message.created_at) }}
                </span>

            </div>

            <!-- Modal Image Viewer -->
            <div v-if="isModalOpen" class="modal" @click="closeImageModal">
                <img :src="modalImage" alt="Expanded image" class="modal-content" @click.stop>
            </div>
        </div>
</template>


<script setup>

defineProps({
    messages: Array,
    currentChatId: Number,
    modalImage: String,
    isModalOpen: Boolean,
    closeImageModal: Function,
    formattedTime: Function // Parentdan kelayotgan funksiya

});
</script>
