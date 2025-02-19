<template>
    <aside class="sidebar" :class="{ open: sidebarVisible }">
        <h2>Chats</h2>
        <button v-for="chat in sidebarChats" :key="chat.id" class="chat-button" @click="loadChat(chat.name, chat.id)">
            {{ chat.name }}
        </button>
    </aside>

    <main class="chat-main" @click="closeSidebar">
        <header>
            <button id="menu-btn" @click.stop="toggleSidebar">☰</button>
            <h1 id="chat-title">{{ chatTitle }}</h1>
        </header>

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


// Reactive variables
const sidebarChats = ref([]);
const chatTitle = ref("Mavjud chatlat");
const sidebarVisible = ref(false);
const messages = ref([]);
const newMessage = ref("");
const chatBox = ref(null);
const currentChatId = ref(null);
const CHATID = ref(null);

function formattedTime(date) {
    const options = { hour: '2-digit', minute: '2-digit', hour12: false };
    return new Date(date).toLocaleTimeString('en-US', options);
}
const fileInput = ref(null);
let selectedFile = null;

// 📂 Fayl tanlash uchun ikon bosilganda
const handleFileClick = () => {
    fileInput.value.click();
};

// 📂 Fayl tanlanganda
const handleFileUpload = (event) => {
    selectedFile = event.target.files[0]; // Faqat bitta faylni saqlash
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
                newMessage.value = ""; // Inputni tozalash
                scrollToBottom();
            })
            .catch(error => console.error(error));
    } else if (selectedFile && !text) {
        // Agar faqat bitta fayl bo'lsa
        let formData = new FormData();
        formData.append("receiver_id", receiver_id);
        formData.append("file", selectedFile); // Bitta faylni yuborish

        axios.post("/send-media", formData, {
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'multipart/form-data'
            }
        })
            .then(response => {
                selectedFile = null; // Faylni tozalash
                scrollToBottom();
            })
            .catch(error => console.error(error));
    } else {
        alert("Iltimos, faqat xabar yoki faqat bitta media yuboring!");
    }
};


const isModalOpen = ref(false); // Modalni ko'rsatish uchun flag
const modalImage = ref(null); // Katta rasmni ko'rsatish uchun

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





// setInterval(getChats, 1000);


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
            // messages.value.push(e.message); // Faqatgina backenddan kelgan ma'lumotni qo'shamiz
        });

});
</script>
<style scoped>
/* Modal styles */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.8);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.modal-content {
    max-width: 90%;
    max-height: 90%;
    border-radius: 10px;
    cursor: zoom-out;
}

/* Agar modal oynasini mobile uchun ham moslashgan qilib ko'rsatish */
@media (max-width: 768px) {
    .modal-content {
        max-width: 100%;
        max-height: 100%;
    }
}











footer .chat-form {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
}

.input-container {
    display: flex;
    align-items: center;
    border: 1px solid #444;
    border-radius: 5px;
    overflow: hidden;
    /* Ichki elementlar tashqariga chiqmasin */
    width: 100%;
    background: #333;
}

.input-container input {
    flex-grow: 1;
    padding: 12px;
    border: none;
    outline: none;
    background: #333;
    color: white;
}

.input-container .icon {
    width: 40px;
    height: 40px;
    cursor: pointer;
    padding: 8px;
    color: #aaa;
    transition: color 0.2s ease;
}

.input-container .icon:hover {
    color: #fff;
}

.input-container button {
    padding: 12px;
    background: #28a745;
    color: white;
    border: none;
    cursor: pointer;
}













body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    display: flex;
    height: 100vh;
    background: #121212;
    color: white;
}

.no-chat-message {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    font-size: 24px;
    color: #333;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.chat-container {
    display: flex;
    width: 100%;
}

.sidebar {
    width: 250px;
    background: #1e1e1e;
    color: white;
    padding: 15px;
    display: block;
}

.chat-button {
    display: block;
    width: 100%;
    padding: 10px;
    margin: 5px 0;
    background: #333;
    color: white;
    border: none;
    cursor: pointer;
    text-align: left;
}

.chat-main {
    flex: 1;
    display: flex;
    flex-direction: column;
    background: #181818;
}

header {
    background: #222;
    color: white;
    padding: 10px;
    display: flex;
    align-items: center;
}

#menu-btn {
    display: none;
    background: none;
    border: none;
    color: white;
    font-size: 24px;
    cursor: pointer;
    margin-right: 10px;
}

.chat-box {
    flex: 1;
    padding: 15px;
    overflow-y: auto;
    background: #242424;
    border-top: 1px solid #333;
    display: flex;
    flex-direction: column;
}

.message {
    padding: 10px;
    margin: 5px;
    border-radius: 5px;
    max-width: 70%;
    word-wrap: break-word;
    display: inline-block;
}

/* Sender message (right) */
.message.right {
    background: #007bff;
    align-self: flex-end;
    text-align: right;
    color: white;
}

/* Receiver message (left) */
.message.left {
    background: #28a745;
    align-self: flex-start;
    text-align: left;
    color: white;
}



@media (max-width: 768px) {
    .sidebar {
        display: none;
        position: absolute;
        width: 250px;
        height: 100%;
        background: #1e1e1e;
        left: 0;
        top: 0;
        transition: transform 0.3s ease-in-out;
        transform: translateX(-100%);
    }

    .sidebar.open {
        display: block;
        /* Sidebar ko‘rinadigan bo‘lishi uchun */
        transform: translateX(0);
        /* Chapdan chiqib kelishi uchun */
    }

    #menu-btn {
        display: block;
    }


    /* Yangi qo'shilgan o'zgarishlar: Chat-box xabarlarining ko'rinishini yaxshilash */
    .chat-box {
        padding: 10px;
    }

    .message {
        max-width: 90%;
        /* Xabarlarning uzunligini kichikroq qilish */
        padding: 8px;
        margin: 4px 0;
    }

    footer input {
        padding: 10px;
    }

    footer button {
        padding: 10px;
    }
}


/* Login konteyneri */
.container {
    width: 100%;
    max-width: 600px;
    /* Formaning maksimal eni */
    padding: 20px;
}

/* Kartochka (Card) */
.card {
    background: #1e1e1e;
    /* Qoramtur kartochka fon rangi */
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    padding: 20px;
    height: auto;
    /* Avtomatik uzunlik */
    max-height: 600px;
    /* Maksimal balandlikni belgilash */
}

/* Form elementlari */
.form-control {
    width: 100%;
    padding: 10px;
    border: 1px solid #444;
    /* Qoramtur chegara */
    border-radius: 5px;
    font-size: 16px;
    background: #333;
    /* Input fon rangi */
    color: white;
}

/* Login tugmasi */
.btn-primary {
    background-color: #28a745;
    /* Yashil rang */
    border: none;
    padding: 10px 15px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    width: 100%;
}

.btn-primary:hover {
    background-color: #218838;
    /* Tugma hover holati */
}

/* Register linki */
.register-link {
    display: block;
    margin-top: 20px;
    text-align: center;
    font-size: 14px;
    color: #007bff;
    /* Ko'k rangli register havolasi */
    text-decoration: none;
}

.register-link:hover {
    text-decoration: underline;
}

/* Kichik ekranlar (Mobil uchun) */
@media (max-width: 768px) {
    .container {
        max-width: 90%;
    }

    .form-check-label {
        font-size: 14px;
    }

    .btn-primary {
        font-size: 14px;
    }
}

/* Register page  */
/* Asosiy konteyner */
.w-full {
    width: 100%;
}

.h-screen {
    height: 100vh;
}

.flex {
    display: flex;
}

.justify-center {
    justify-content: center;
}

.items-center {
    align-items: center;
}

/* Forma konteyneri */
.form-container {
    background-color: #1e1e1e;
    /* Qoramtur fon */
    border-radius: 10px;
    padding: 30px;
    max-width: 450px;
    width: 100%;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
}

/* Formaning sarlavhasi */
h2 {
    font-size: 1.5rem;
    color: #fff;
    margin-bottom: 20px;
}

/* Input maydonlari */
.input-field {
    width: 100%;
    padding: 12px;
    margin: 10px 0;
    border: 1px solid #444;
    /* Qoramtur chegara */
    background-color: #333;
    /* Qoramtur fon */
    color: #fff;
    border-radius: 5px;
    font-size: 16px;
}

/* Xato xabarlari */
.text-red-500 {
    color: #f87171;
    /* Qizil rangli xato xabarlar */
    font-size: 12px;
}

/* Tugma */
.btn {
    width: 100%;
    padding: 12px;
    background-color: #28a745;
    /* Yashil rang */
    border: none;
    border-radius: 5px;
    color: white;
    font-size: 16px;
    cursor: pointer;
}

.btn:hover {
    background-color: #218838;
    /* Hover holati */
}

/* Footer bo'limi */
.footer {
    margin-top: 15px;
    text-align: center;
}

.footer a {
    color: #4db8ff;
    /* Ko'k rangli havola */
    text-decoration: none;
}

.footer a:hover {
    text-decoration: underline;
}

/* Kichik ekranlar (Mobil qurilmalar uchun) */
@media (max-width: 768px) {
    .form-container {
        padding: 20px;
    }

    h2 {
        font-size: 1.25rem;
    }

    .input-field {
        font-size: 14px;
    }

    .btn {
        font-size: 14px;
    }
}




















/* Login konteyneri */
.container {
    width: 100%;
    max-width: 600px; /* Formaning maksimal eni */
    padding: 20px;
}

/* Kartochka (Card) */
.card {
    background: #1e1e1e;  /* Qoramtur kartochka fon rangi */
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    padding: 20px;
    height: auto;  /* Avtomatik uzunlik */
    max-height: 600px; /* Maksimal balandlikni belgilash */
}

/* Form elementlari */
.form-control {
    width: 100%;
    padding: 10px;
    border: 1px solid #444;  /* Qoramtur chegara */
    border-radius: 5px;
    font-size: 16px;
    background: #333;  /* Input fon rangi */
    color: white;
}

/* Login tugmasi */
.btn-primary {
    background-color: #28a745;  /* Yashil rang */
    border: none;
    padding: 10px 15px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    width: 100%;
}

.btn-primary:hover {
    background-color: #218838;  /* Tugma hover holati */
}

/* Register linki */
.register-link {
    display: block;
    margin-top: 20px;
    text-align: center;
    font-size: 14px;
    color: #007bff;  /* Ko'k rangli register havolasi */
    text-decoration: none;
}

.register-link:hover {
    text-decoration: underline;
}

/* Kichik ekranlar (Mobil uchun) */
@media (max-width: 768px) {
    .container {
        max-width: 90%;
    }

    .form-check-label {
        font-size: 14px;
    }

    .btn-primary {
        font-size: 14px;
    }
}

/* Register page  */
/* Asosiy konteyner */
.w-full {
    width: 100%;
}
.h-screen {
    height: 100vh;
}
.flex {
    display: flex;
}
.justify-center {
    justify-content: center;
}
.items-center {
    align-items: center;
}

/* Forma konteyneri */
.form-container {
    background-color: #1e1e1e;  /* Qoramtur fon */
    border-radius: 10px;
    padding: 30px;
    max-width: 450px;
    width: 100%;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
}

/* Formaning sarlavhasi */
h2 {
    font-size: 1.5rem;
    color: #fff;
    margin-bottom: 20px;
}

/* Input maydonlari */
.input-field {
    width: 100%;
    padding: 12px;
    margin: 10px 0;
    border: 1px solid #444; /* Qoramtur chegara */
    background-color: #333; /* Qoramtur fon */
    color: #fff;
    border-radius: 5px;
    font-size: 16px;
}

/* Xato xabarlari */
.text-red-500 {
    color: #f87171;  /* Qizil rangli xato xabarlar */
    font-size: 12px;
}

/* Tugma */
.btn {
    width: 100%;
    padding: 12px;
    background-color: #28a745;  /* Yashil rang */
    border: none;
    border-radius: 5px;
    color: white;
    font-size: 16px;
    cursor: pointer;
}
.btn:hover {
    background-color: #218838;  /* Hover holati */
}

/* Footer bo'limi */
.footer {
    margin-top: 15px;
    text-align: center;
}
.footer a {
    color: #4db8ff;  /* Ko'k rangli havola */
    text-decoration: none;
}
.footer a:hover {
    text-decoration: underline;
}

/* Kichik ekranlar (Mobil qurilmalar uchun) */
@media (max-width: 768px) {
    .form-container {
        padding: 20px;
    }

    h2 {
        font-size: 1.25rem;
    }

    .input-field {
        font-size: 14px;
    }

    .btn {
        font-size: 14px;
    }
}


</style>
