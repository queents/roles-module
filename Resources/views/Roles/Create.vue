<script setup>
import {onMounted, ref} from "vue";
import Container from '@@/Components/Container.vue';
import Header from '@@/Layouts/Includes/Header.vue';
import {Inertia} from '@inertiajs/inertia';
import {buildCreateForm} from '@@/Composables/buildCreateForm.js';
import {submitForm} from '@@/Composables/submitForm.js';
import {useTrans} from '@@/Composables/useTrans.js';
import ViltText from '$$/ViltText.vue'
import {useLayoutStore} from '@@/Stores/layout.js';

let form = ref({});
let selectAll = ref(false);
let permValue = ref({});
let tableValue = ref({});
let tab = ref("resources");
const {trans} = useTrans();

let createRows = buildCreateForm();

const props = defineProps({
    rows: Object,
    errors: Object,
    data: Object,
    url: String,
    perm: Object
})

form.value.permissions = [];

onMounted(()=>{
    let getPermKey = Object.keys(props.perm);
    for(let i=0; i<getPermKey.length; i++){
        for(let r=0; r<props.perm[getPermKey[i]].length; r++){
            permValue.value[props.perm[getPermKey[i]][r].name] = false;
        }
    }
})

function checkAllItems(value){
    selectAll.value = !selectAll.value;

    let getPerm = props.perm;
    form.value.permissions = [];
    for(let i=0; i<Object.keys(getPerm).length; i++){
        tableValue.value[Object.keys(getPerm)[i]] = value;
        props.perm[Object.keys(getPerm)[i]].forEach(function (getItem){
            permValue.value[getItem.name] = value;
            togglePerm(getItem, !value)
        });
    }
}

function setValueTable(table){
    tableValue.value[table] = !tableValue.value[table];
    if(!tableValue.value[table]){
        let getPerm = props.perm[table];
        getPerm.forEach(function (getItem){
            permValue.value[getItem.name] = false;
            togglePerm(getItem, true)
        });
    }
    else {
        let getPerm = props.perm[table];
        getPerm.forEach(function (getItem){
            permValue.value[getItem.name] = true;
            togglePerm(getItem)
        });
    }

}

function togglePerm(item, remove=false){
    if(remove){
        form.value.permissions.forEach(function (getItem, index){
            if(item.id === getItem.id){
                form.value.permissions.splice(index, 1)
            }
        });
    }
    else {
        let isItemExists = false;
        form.value.permissions.forEach(function (getItem){
            if(item.id === getItem.id){
                isItemExists = true;
            }
        });

        if(!isItemExists){
            form.value.permissions.push(item);
        }
    }
}

function save(redirect=false){
    let onSubmit = submitForm('.store', form.value, false, redirect);
    if(onSubmit){
        form.value = {};
    }
}
function back(){
    Inertia.visit(route(props.url+'.index'));
}

// Check if The Sidebar is expanded or not
const layoutStore = useLayoutStore();
layoutStore.Breadcrumbs = [];

layoutStore.setBreadcrumbs({
    route: route("dashboard"),
    label: trans('global.dashboard')
});
layoutStore.setBreadcrumbs({
    route: route(props.url +".index"),
    label: "Roles"
});
layoutStore.setBreadcrumbs({
    route: route( props.url +".create"),
    label: "Create Role"
});
</script>
<template>
    <Container>
        <template #header>
            <Header title="Create Role" />
        </template>
        <template #body>
            <form action="" enctype="multipart/form-data">
                <div class="grid grid-cols-1 lg:grid-cols-2 filament-forms-component-container gap-6">
                    <div class="col-span-full">
                        <div>
                            <div class="grid grid-cols-1 lg:grid-cols-2 filament-forms-component-container gap-6">
                                <div class="col-span-full">
                                    <div class="filament-forms-card-component p-6 bg-white rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-800">
                                        <div class="grid grid-cols-1 sm:grid-cols-2  lg:grid-cols-3   filament-forms-component-container gap-6">
                                            <ViltText :row="createRows[0]" v-model="form[createRows[0].name]" :message="props.errors[createRows[0].name]"/>
                                            <ViltText :row="createRows[1]" v-model="form[createRows[1].name]" :message="props.errors[createRows[1].name]"/>

                                            <div class="col-span-1">
                                                <div class="filament-forms-field-wrapper">
                                                    <div class="space-y-2">
                                                        <div class="flex items-center justify-between space-x-2 rtl:space-x-reverse">
                                                            <label @click.prevent="checkAllItems(!selectAll)"  class="filament-forms-field-wrapper-label inline-flex items-center space-x-3 rtl:space-x-reverse" for="data.select_all">
                                                                <button  role="switch" aria-checked="false" :class="{
                                                                                    'bg-primary-600': selectAll,
                                                                                    'bg-gray-200  dark:bg-white/10 ': ! selectAll,
                                                                                }"
                                                                         id="data.select_all"
                                                                         type="button"
                                                                         class="filament-forms-toggle-component relative inline-flex shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500 disabled:opacity-70 disabled:cursor-not-allowed disabled:pointer-events-none border-gray-300 bg-gray-200 dark:bg-white/10">
                                                                                <span class="pointer-events-none relative inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 ease-in-out transition duration-200 translate-x-0" :class="{
                                                                                        'translate-x-5 rtl:-translate-x-5': selectAll,
                                                                                        'translate-x-0': ! selectAll,
                                                                                    }">
                                                                                    <span class="absolute inset-0 h-full w-full flex items-center justify-center transition-opacity opacity-100 ease-in duration-200" aria-hidden="true" :class="{
                                                                                            'opacity-0 ease-out duration-100': selectAll,
                                                                                            'opacity-100 ease-in duration-200': ! selectAll,
                                                                                        }">
                                                                                        <svg class="h-3 w-3 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                                                          <path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0110 1.944zM11 14a1 1 0 11-2 0 1 1 0 012 0zm0-7a1 1 0 10-2 0v3a1 1 0 102 0V7z" clip-rule="evenodd"></path>
                                                                                        </svg>
                                                                                    </span>

                                                                                    <span class="absolute inset-0 h-full w-full flex items-center justify-center transition-opacity opacity-0 ease-out duration-100" aria-hidden="true" :class="{
                                                                                            'opacity-100 ease-in duration-200': selectAll,
                                                                                            'opacity-0 ease-out duration-100': ! selectAll,
                                                                                        }">
                                                                                        <svg class="h-3 w-3 text-primary-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                                                          <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                                                        </svg>
                                                                                    </span>
                                                                                </span>

                                                                        </button>

                                                                        <span class="text-sm font-medium leading-4 text-gray-700 dark:text-gray-300">
                                                                            Select All
                                                                        </span>
                                                            </label>
                                                        </div>
                                                        <div class="filament-forms-field-wrapper-helper-text text-sm text-gray-600 dark:text-gray-300">
                                                            <p>Enable all Permissions currently <span class="text-primary font-medium">Enabled</span> for this role</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-span-full">
                        <div class="filament-forms-tabs-component rounded-xl shadow-sm border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-700">
                            <div
                                aria-labelledby="-resources-tab"
                                id="-resources-tab" role="tabpanel"
                                tabindex="0"
                                :class="{
                                    'invisible h-0 p-0 overflow-y-hidden': tab === 'resource',
                                    'p-6': tab !== 'resource'
                                }"
                                class="filament-forms-tabs-component-tab focus:outline-none p-6">
                                <div class="grid grid-cols-1 filament-forms-component-container gap-6">
                                    <div class="col-span-full">
                                        <div>
                                            <div class="grid grid-cols-1 sm:grid-cols-2  lg:grid-cols-2   filament-forms-component-container gap-6">
                                                <div class="col-span-1" v-for="(item, index) in perm" :key="index">
                                                    <div class="filament-forms-card-component p-6 bg-white rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-800 shadow-sm">
                                                        <div class="grid grid-cols-1 lg:grid-cols-2 filament-forms-component-container gap-6">
                                                            <div  class="col-span-1">
                                                                <div class="filament-forms-field-wrapper">

                                                                    <div class="space-y-2">
                                                                        <div class="flex items-center justify-between space-x-2 rtl:space-x-reverse">
                                                                            <label class="filament-forms-field-wrapper-label inline-flex items-center space-x-3 rtl:space-x-reverse" for="data.blog::author">
                                                                                <button
                                                                                    @click.prevent="setValueTable(index)"
                                                                                    role="switch"
                                                                                    aria-checked="false"
                                                                                    :class="{
                                                                                        'bg-primary-600': tableValue[index],
                                                                                        'bg-gray-200  dark:bg-white/10 ': ! tableValue[index],
                                                                                    }"
                                                                                    id="data.blog::author"
                                                                                    type="button"
                                                                                    class="filament-forms-toggle-component relative inline-flex shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500 disabled:opacity-70 disabled:cursor-not-allowed disabled:pointer-events-none border-gray-300 bg-gray-200 dark:bg-white/10">
                                                                                            <span class="pointer-events-none relative inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 ease-in-out transition duration-200 translate-x-0"
                                                                                                  :class="{
                                                                                                    'translate-x-5 rtl:-translate-x-5': tableValue[index],
                                                                                                    'translate-x-0': ! tableValue[index],
                                                                                                }">
                                                                                                <span
                                                                                                    class="absolute inset-0 h-full w-full flex items-center justify-center transition-opacity opacity-100 ease-in duration-200"
                                                                                                      aria-hidden="true"
                                                                                                      :class="{
                                                                                                        'opacity-0 ease-out duration-100': tableValue[index],
                                                                                                        'opacity-100 ease-in duration-200': ! tableValue[index],
                                                                                                    }">
                                                                                                    <svg class="h-3 w-3 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                                                                      <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                                                                                                    </svg>
                                                                                                </span>

                                                                                                <span class="absolute inset-0 h-full w-full flex items-center justify-center transition-opacity opacity-0 ease-out duration-100" aria-hidden="true"
                                                                                                      :class="{
                                                                                                        'opacity-100 ease-in duration-200': tableValue[index],
                                                                                                        'opacity-0 ease-out duration-100': ! tableValue[index],
                                                                                                    }">
                                                                                                    <svg class="h-3 w-3 text-primary-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                                                                      <path d="M10 2a5 5 0 00-5 5v2a2 2 0 00-2 2v5a2 2 0 002 2h10a2 2 0 002-2v-5a2 2 0 00-2-2H7V7a3 3 0 015.905-.75 1 1 0 001.937-.5A5.002 5.002 0 0010 2z"></path>
                                                                                                    </svg>
                                                                                                </span>
                                                                                            </span>
                                                                                </button>

                                                                                <span class="text-sm font-medium leading-4 text-gray-700 dark:text-gray-300 capitalize">
                                                                                    {{index.replace('_', ' ').replace('_', ' ')}}
                                                                                </span>
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                            </div>

                                                            <div class="col-span-full">
                                                                <fieldset class="filament-forms-fieldset-component rounded-xl shadow-sm border border-gray-300 p-6 dark:border-gray-600 dark:text-gray-200 text-primary-600" style="border-color:var(--primary)">
                                                                    <legend class="text-sm leading-tight font-medium px-2 -ml-2">
                                                                        Permissions
                                                                    </legend>

                                                                    <div class="grid grid-cols-2 lg:grid-cols-2 xl:grid-cols-2  filament-forms-component-container gap-6">
                                                                        <div class="col-span-1" v-for="(getItem, key) in item" :key="key">
                                                                            <div class="filament-forms-field-wrapper">
                                                                                <div class="space-y-2">
                                                                                    <div class="flex items-center justify-between space-x-2 rtl:space-x-reverse">
                                                                                        <label class="filament-forms-field-wrapper-label inline-flex items-center space-x-3 rtl:space-x-reverse">
                                                                                            <input
                                                                                                   id="data.view_blog::author"
                                                                                                   type="checkbox"
                                                                                                   v-model="permValue[getItem.name]"
                                                                                                   @change="togglePerm(getItem, permValue[getItem.name])"
                                                                                                   class="filament-forms-checkbox-component text-primary-600 transition duration-75 rounded shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500 disabled:opacity-70 dark:bg-gray-700 dark:checked:bg-primary-500 border-gray-300 dark:border-gray-600 text-primary-600">
                                                                                            <span class="text-sm font-medium leading-4 text-gray-700 dark:text-gray-300 capitalize">
                                                                                                {{getItem.name.replace('_'+index, '').replace('_', ' ').replace('_', ' ')}}
                                                                                            </span>
                                                                                        </label>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </fieldset>
                                                            </div>
                                                        </div>

                                                    </div>

                                                </div>
                                            </div>

                                        </div>

                                    </div>
                                </div>

                            </div>

                            <div
                                aria-labelledby="-pages-tab"
                                 id="-pages-tab"
                                 role="tabpanel"
                                 tabindex="0"
                                 :class="{
                                'invisible h-0 p-0 overflow-y-hidden': tab === 'page',
                                'p-6': tab !== 'page'
                            }"
                                class="filament-forms-tabs-component-tab focus:outline-none invisible h-0 p-0 overflow-y-hidden">
                                <div class="grid grid-cols-1 filament-forms-component-container gap-6">
                                    <div class="col-span-full">
                                        <div>
                                            <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-4 filament-forms-component-container gap-6">
                                                <div class="col-span-1">
                                                    <div>
                                                        <div class="grid grid-cols-1   lg:grid-cols-1   filament-forms-component-container gap-6">
                                                            <div wire:key="AcMDA2dy7B6TXy2beOjw.data.page_Artisan.Filament\Forms\Components\Checkbox" class=" col-span-1     ">
                                                                <div class="filament-forms-field-wrapper">

                                                                    <div class="space-y-2">
                                                                        <div class="flex items-center justify-between space-x-2 rtl:space-x-reverse">
                                                                            <label class="filament-forms-field-wrapper-label inline-flex items-center space-x-3 rtl:space-x-reverse" for="data.page_Artisan">
                                                                                <input id="data.page_Artisan" type="checkbox" wire:model="data.page_Artisan" dusk="filament.forms.data.page_Artisan" class="filament-forms-checkbox-component text-primary-600 transition duration-75 rounded shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500 disabled:opacity-70 dark:bg-gray-700 dark:checked:bg-primary-500 border-gray-300 dark:border-gray-600">

                                                                                <span class="text-sm font-medium leading-4 text-gray-700 dark:text-gray-300">
        Artisan

            </span>


                                                                            </label>

                                                                        </div>




                                                                    </div>
                                                                </div>

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="py-2 px-2 flex justify-start space-x-2">
                    <button
                            class="filament-button inline-flex items-center justify-center py-1 gap-1 font-medium rounded-lg border transition-colors focus:outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset dark:focus:ring-offset-0 min-h-[2.25rem] px-4 text-sm text-white shadow focus:ring-white border-transparent bg-primary-600 hover:bg-primary-500 focus:bg-primary-700 focus:ring-offset-primary-700 filament-page-button-action"
                            @click.prevent="save(true)">Create</button>
                    <button
                        class="filament-button inline-flex items-center justify-center py-1 gap-1 font-medium rounded-lg border transition-colors focus:outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset dark:focus:ring-offset-0 min-h-[2.25rem] px-4 text-sm text-gray-800 bg-white border-gray-300 hover:bg-gray-50 focus:ring-primary-600 focus:text-primary-600 focus:bg-primary-50 focus:border-primary-600 dark:bg-gray-800 dark:hover:bg-gray-700 dark:border-gray-600 dark:hover:border-gray-500 dark:text-gray-200 dark:focus:text-primary-400 dark:focus:border-primary-400 dark:focus:bg-gray-800 filament-page-button-action"
                            @click.prevent="save">Create & create another</button>
                    <button class="filament-button inline-flex items-center justify-center py-1 gap-1 font-medium rounded-lg border transition-colors focus:outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset dark:focus:ring-offset-0 min-h-[2.25rem] px-4 text-sm text-gray-800 bg-white border-gray-300 hover:bg-gray-50 focus:ring-primary-600 focus:text-primary-600 focus:bg-primary-50 focus:border-primary-600 dark:bg-gray-800 dark:hover:bg-gray-700 dark:border-gray-600 dark:hover:border-gray-500 dark:text-gray-200 dark:focus:text-primary-400 dark:focus:border-primary-400 dark:focus:bg-gray-800 filament-page-button-action"
                            @click.prevent="back()">Cancel</button>
                </div>
            </form>
        </template>
    </Container>
</template>
<script>
import AppLayout from "@@/Layouts/AppLayout.vue";

export default {
    layout: AppLayout,
}
</script>

