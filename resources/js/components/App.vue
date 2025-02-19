<template>

    <sidebar  :sidebarVisible = "sidebarVisible" :sidebarChats = "sidebarChats" @chat-selected="loadChat"></sidebar>

    <main class="chat-main" @click="closeSidebar">

      <!-- <Header  :chatTitle = "chatTitle"  ></header> -->
        <header>
            <button id="menu-btn" @click.stop="toggleSidebar">☰</button>
            <h1 id="chat-title">{{ chatTitle }}</h1>
        </header>


        <ChatBoxComponent :messages="messages"  :openImageModal="openImageModal" :currentChatId="currentChatId" :modalImage="modalImage" :isModalOpen="isModalOpen"  :closeImageModal="closeImageModal" :formattedTime = "formattedTime"></ChatBoxComponent>

        <footer v-if="CHATID != null">
            <form @submit.prevent="sendMessage" enctype="multipart/form-data" class="chat-form">
                <div class="input-container">
                    <input v-model="newMessage" type="text" placeholder="Xabarni yozing .......">
                    <svg @click="handleFileClick" class="icon" fill="none" stroke-width="1.5" stroke="currentColor"
                        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13">
                        </path>
                    </svg>
                    <input type="file" @change="handleFileUpload" multiple ref="fileInput" style="display: none;">
                </div>
                <button type="submit">Send</button>
            </form>
        </footer>
        <div v-else class="no-chat-message">
            Select a chat to start messaging
        </div>

    </main>
</template>
<script setup>
import axios from 'axios';
import { ref, onMounted, nextTick } from 'vue';
import sidebar from './sidebar.vue';
import ChatBoxComponent from './chatBox.vue';


// Reactive variables
const sidebarChats = ref([]);
const chatTitle = ref("Mavjud chatlat");
const sidebarVisible = ref(false);
const messages = ref([]);
const newMessage = ref("");
const chatBox = ref(null);
const currentChatId = ref(null);
const CHATID = ref(null);
const fileInput = ref(null);
let selectedFile = null;
const isModalOpen = ref(false);
const modalImage = ref(null); 

function formattedTime(date) {
    const options = { hour: '2-digit', minute: '2-digit', hour12: false };
    return new Date(date).toLocaleTimeString('en-US', options);
}

// 📂 Fayl tanlash uchun ikon bosilganda
const handleFileClick = () => {
    fileInput.value.click();
};

// 📂 Fayl tanlanganda
const handleFileUpload = (event) => {
    selectedFile = event.target.files[0]; 
};

// Send new message
const sendMessage = () => {
    const receiver_id = currentChatId.value;
    const text = newMessage.value.trim();
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    if (text && !selectedFile) {
        // Agar faqat matn bo'lsa
        axios.post("/send-message", { receiver_id, text }, {
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        })
            .then(response => {
                newMessage.value = "";
                scrollToBottom();
            })
            .catch(error => console.error(error));
    } else if (selectedFile && !text) {
        // Agar faqat bitta fayl bo'lsa
        let formData = new FormData();
        formData.append("receiver_id", receiver_id);
        formData.append("file", selectedFile); 

        axios.post("/send-media", formData, {
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'multipart/form-data'
            }
        })
            .then(response => {
                selectedFile = null; 
                scrollToBottom();
            })
            .catch(error => console.error(error));
    } else {
        alert("Iltimos, faqat xabar yoki faqat bitta media yuboring!");
    }
};

// Rasmni kattalashtirish
const openImageModal = (imageUrl) => {
    modalImage.value = imageUrl;
    isModalOpen.value = true;
};

// Modalni yopish
const closeImageModal = () => {
    isModalOpen.value = false;
};
// SidebarChats function
function SidebarChats() {
    const url = "/sidebar-chats";

    axios.get(url)
        .then(response => {
            sidebarChats.value = response.data;
        })
        .catch(error => {
            console.error(error);
        });
}

// Toggle sidebar visibility
const toggleSidebar = () => {
    sidebarVisible.value = !sidebarVisible.value;

};
const closeSidebar = () => {
    if (window.innerWidth <= 768) {
        sidebarVisible.value = false;
    }
};
// Load chat and messages
const loadChat = (chatName, chatId) => {
    chatTitle.value = chatName;
    CHATID.value = chatId;
    getChats();

    if (window.innerWidth <= 768) {
        sidebarVisible.value = false;
    }
};
function getChats() {
    const url = `/chat/${CHATID.value}`;
    currentChatId.value = CHATID.value;

    axios.get(url)
        .then(response => {
            console.log(response.data);
            messages.value = response.data;
            nextTick(() => {
                if (chatBox.value) {
                    chatBox.value.scrollTop = chatBox.value.scrollHeight;
                }
            });
        })
        .catch(error => {
            console.error(error);
        });
}
// Scroll to bottom after sending a message
const scrollToBottom = () => {
    const chatBoxElement = document.querySelector(".chat-box");
    chatBoxElement.scrollTop = chatBoxElement.scrollHeight;
};

onMounted(() => {
    SidebarChats();

    document.addEventListener("click", closeSidebar);
    window.Echo.private(`chat.0-0`)
        .listen('GotMessage', (e) => {
            getChats();
        });

});
</script>