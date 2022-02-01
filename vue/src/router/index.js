import {createRouter, createWebHistory} from "vue-router";
import Dashboard from "../views/Dashboard.vue";
import Login from "../views/Login.vue";
import Register from "../views/Register.vue";
import DefaultLayout from "../components/DefaultLayout.vue";
import Surveys from "../views/Surveys.vue";
import store from "../store";
import AuthLayout from "../components/AuthLayout.vue";

const routes = [
    {
        path : '/',
        redirect : '/dashboard',
        component : DefaultLayout,
        meta : {requiresAuth : true},
        children : [
            {path: '/dashboard', name : 'Dashboard', component : Dashboard},
            {path: '/surveys', name : 'Surveys', component : Surveys},

        ]
    },
    {
        path : '/auth',
        redirect: '/login',
        name : 'Auth',
        component: AuthLayout,
        meta: {isGuest : true},
        children: [
            {
                path : '/login',
                name : 'Login',
                component : Login
            },
            {
                path : '/register',
                name : 'Register',
                component : Register
            }
        ]
    },


];

const router = createRouter( {

    history : createWebHistory(),
    routes

})

// before the user reaches a secured page we check if the user have a token, if token exists we allow him
// to continue to the path, otherwise we send him to login page
router.beforeEach((to,from,next) => {

    if (to.meta.requiresAuth && !store.state.user.token){
        next({name: 'Login'})
    } else if(to.meta.isGuest && store.state.user.token) {
       next({name : 'Dashboard'})
    } else {
        next();
    }

})





export default router;
