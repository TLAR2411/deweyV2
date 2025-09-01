import axios from "axios";
import { useStore } from "@/store";

axios.defaults.withCredentials = true;

export const api = axios.create({
  baseURL: `${import.meta.env.VITE_API_BASE_URL}/api/`,
  headers: {
    "Content-Type": "application/json",
    Accept: "application/json",
    "Access-Control-Allow-Origin": `${import.meta.env.VITE_API_BASE_URL}`,
  },
});

// Add a request interceptor
api.interceptors.request.use(function (config) {
  // @ts-ignore
  config.headers.Authorization = `Bearer ${useStore().token}`;
  return config;
});
