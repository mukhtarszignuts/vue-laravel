// Import the functions you need from the SDKs you need
import { initializeApp } from "firebase/app";
// import { getAnalytics } from "firebase/analytics";
import { getMessaging, onMessage } from "firebase/messaging";
import avatar1 from "@images/avatars/avatar-1.png";


// TODO: Add SDKs for Firebase products that you want to use
// https://firebase.google.com/docs/web/setup#available-libraries

// Your web app's Firebase configuration
// For Firebase JS SDK v7.20.0 and later, measurementId is optional
const firebaseConfig = {
    apiKey: "AIzaSyAZR-lxhtKo7T_EQRvMZFlh9_k5q8PQLds",
    authDomain: "chat-vue-ab3fb.firebaseapp.com",
    projectId: "chat-vue-ab3fb",
    storageBucket: "chat-vue-ab3fb.appspot.com",
    messagingSenderId: "906188459644",
    appId: "1:906188459644:web:8f6bde63b7ae23b14343f9",
    measurementId: "G-BEV6SS884J"
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);

// const analytics = getAnalytics(app);
// Initialize Messaging
const messaging = getMessaging(app);

export const initializeMessageListener = () => {
  onMessage(messaging, (payload) => {
    
    const notificationTitle = payload?.notification?.title;
    const notificationOptions = {
      body: payload.notification?.body,
      icon: payload.notification?.icon,
      // badge: avatar1,
    };

    new Notification(notificationTitle, notificationOptions);
  });
};

export { messaging };
