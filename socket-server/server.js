const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
const cors = require('cors');

const app = express();
app.use(cors()); // للسماح بالوصول من أي دومين (للتطوير)

const server = http.createServer(app);
const io = new Server(server, {
    cors: {
        origin: "*", // السماح لجميع العملاء بالاتصال
    },
});

// استقبال الاتصالات من المستخدمين
io.on('connection', (socket) => {
    console.log(`User connected: ${socket.id}`);

    // استقبال الرسائل
    socket.on('sendMessage', (data) => {
        console.log('Message received:', data);

        // بث الرسالة إلى جميع العملاء
        io.emit('receiveMessage', data);
    });

    socket.on('disconnect', () => {
        console.log(`User disconnected: ${socket.id}`);
    });
});

server.listen(3000, () => {
    console.log('Socket.IO server is running on port 3000');
});
