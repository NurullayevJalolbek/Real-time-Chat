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
                {{ message.text }}
            </div>
        </div>




        <footer v-if="CHATID != null">
            <form @submit.prevent="sendMessage">
                <input v-model="newMessage" type="text" placeholder="Xabarni yozing .......">
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
import { ref, onMounted, watch, nextTick } from 'vue';
import Echo from 'laravel-echo';


// Reactive variables
const sidebarChats = ref([]);
const chatTitle = ref("Mavjud chatlat");
const sidebarVisible = ref(false);
const messages = ref([]);
const newMessage = ref("");
const chatBox = ref(null);
const currentChatId = ref(null);
const CHATID = ref(null);

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
            console.log(response.data)
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

// Send new message
const sendMessage = () => {
    const receiver_id = currentChatId.value;
    const text = newMessage.value;
    const url = "/send-message";
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    axios.post(url, { 'receiver_id': receiver_id, 'text': text }, {
        headers: {
            'X-CSRF-TOKEN': csrfToken
        }
    })
        .then(response => {
            newMessage.value = "";
            scrollToBottom();
        })
        .catch(error => {
            console.error(error);
        });
};

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
            // console.log(e);
            getChats();
            // messages.value.push(e.message); // Faqatgina backenddan kelgan ma'lumotni qo'shamiz
        });

});
</script>
<style scoped>
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


footer form {
    display: flex;
    align-items: center;
}

footer input {
    flex: 1;
    padding: 12px;
    border: 1px solid #444;
    border-radius: 5px 0 0 5px;
    /* Chap taraf yumaloq */
    background: #333;
    color: white;
}

footer button {
    padding: 12px;
    background: #28a745;
    color: white;
    border: 1px solid #444;
    border-left: none;
    /* Chegaraning takrorlanishini oldini oladi */
    border-radius: 0 5px 5px 0;
    /* O'ng taraf yumaloq */
    cursor: pointer;
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
</style>
