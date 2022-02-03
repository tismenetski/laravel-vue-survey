<template>
    <page-component>
        <template v-slot:header>
           <div class="flex justify-between items-center">
               <h1 class="text-3xl font-bold text-gray-900">Surveys</h1>
               <router-link :to="{name : 'SurveyCreate'}" class="py-2 px-3 text-white bg-emerald-500 rounded-md hover:bg-emerald-600">
                   <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 -mt-1 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                   </svg>
                   Add New Survey</router-link>

           </div>
        </template>
        <div v-if="surveys.loading" class="flex justify-center">Loading...</div>
        <div v-else class="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3">
            <survey-list-item v-for="(survey,index) in surveys.data" :key="survey.id" :survey="survey" class="opacity-0 animate-fade-in-down" :style="{animationDelay :`${index * 0.1}s` }" @delete="deleteSurvey(survey)"  />
        </div>
    </page-component>
<!--    This uses the page component, page component have prop named title , so we pass the title, everything between the page-component start and end will be the content of slot   -->
</template>

<script setup>
import store from '../store'
import {computed} from "vue";
import PageComponent from '../components/PageComponent.vue'
import SurveyListItem from "../components/SurveyListItem.vue";

const surveys = computed(() => store.state.surveys);

function deleteSurvey(survey) {
    if (confirm('Are you sure you want to delete this survey? Operation cannot be undone')) {
        store.dispatch('deleteSurvey', survey.id).then(() => {
            store.dispatch('getSurveys');
        })
    }
}

store.dispatch('getSurveys');


</script>
