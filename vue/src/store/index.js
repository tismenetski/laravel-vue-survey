import {createStore} from "vuex";
import axiosClient from "../axios";



const store = createStore(
    {
        state : {
            user : {
                data :  {},
                token : sessionStorage.getItem('TOKEN'),
            },
            currentSurvey : {
                loading : false,
                data : {}
            },
            surveys : {loading : false, data : []},
            questionTypes : ['text', 'select', 'radio' , 'checkbox' , 'textarea'],
            notification : {
                show : false,
                type : null,
                message : null
            }
        },

        getters : {},

        actions : {
            register({commit}, user) {
                return axiosClient.post('/register', user)
                    .then(({data}) => {
                        commit('setUser', data);
                        return data;
                    })
            },

            login({commit}, user) {
                return axiosClient.post('/login', user)
                    .then(({data}) => {
                            commit('setUser', data);
                        return data;
                    })
            },
            logout({commit}) {
                return axiosClient.post('/logout')
                    .then(response => {
                        commit('logout');
                        return response;
                    })
            },
            saveSurvey({commit}, survey) {
                delete survey.image_url
                let response;
                if (survey.id) {
                    response = axiosClient.put(`/survey/${survey.id}`, survey)
                        .then((res) => {
                            commit('setCurrentSurvey',res.data);
                            return res;
                        })
                } else {
                    response = axiosClient.post('/survey' , survey)
                        .then((res) => {
                            commit('setCurrentSurvey', res.data);
                            return res;
                        })
                }
                return response;
            },
            getSurvey({commit} , id) {
                commit('setCurrentSurveyLoading', true);
                return axiosClient.get(`/survey/${id}`)
                    .then((res) => {
                        commit('setCurrentSurvey', res.data);
                        commit('setCurrentSurveyLoading' , false);
                        return res;
                    })
                    .catch((err) => {
                        commit('setCurrentSurveyLoading' , false);
                        throw err;
                    })
            },
            deleteSurvey({commit}, id) {
                return axiosClient.delete(`/survey/${id}`)
                    .then((res) => {
                        // commit('deleteSurvey',id);
                        return res;
                    })
            },
            getSurveys({commit}) {
                commit('setSurveysLoading', true);
                return axiosClient.get('/survey').then((res) => {
                    commit('setSurveysLoading', false);
                    commit('setSurveys' , res.data);
                    return res;
                })
            }
        },

        mutations : {
            logout : (state) => {
                state.user.data = {};
                state.user.token = null;
            },
            setUser : (state,userData) => {
                state.user.token = userData.token;
                state.user.data = userData.user;
                sessionStorage.setItem('TOKEN' , userData.token);
            },
            // saveSurvey(state,survey) {
            //     state.surveys = [...state.surveys,survey.data];
            // },
            // updateSurvey(state,survey) {
            //     state.surveys = state.surveys.map((s) => {
            //         if (s.id === survey.data.id) {
            //             return survey.data;
            //         }
            //         return s;
            //     })
            // },
            setCurrentSurvey(state,survey) {
                state.currentSurvey.data  = survey.data;
            },
            setCurrentSurveyLoading(state,loading) {
                state.currentSurvey.loading = loading;
            },
            setSurveysLoading(state,loading) {
                state.surveys.loading = loading;
            },
            setSurveys(state,surveys) {
                state.surveys.data = surveys.data;
            },
            notify(state,{message,type}) {
                state.notification.show = true;
                state.notification.type = type;
                state.notification.message = message;
                setTimeout(() => {
                    state.notification.show = false;
                },3000)
            }
            // deleteSurvey(state,id) {
            //     state.surveys.data.filter((s) => s.id !== id);
            // }
        },

        modules : {}
    }
)

export default store;
