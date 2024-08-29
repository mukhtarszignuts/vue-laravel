import axiosIns from "@/plugins/axios";
import axios from "@axios";
import type { Event, NewEvent } from "./types";
import moment from "moment";
export const useCalendarStore = defineStore("calendar", {
  // arrow function recommended for full type inference
  state: () => ({
    availableCalendars: [
      {
        id:'P',
        color: "error",
        label: "Personal",
      },
      {
        id:'B',
        color: "primary",
        label: "Business",
      },
      {
        id:'F',
        color: "warning",
        label: "Family",
      },
      {
        id:'H',
        color: "success",
        label: "Holiday",
      },
      {
        id:'E',
        color: "info",
        label: "ETC",
      },
    ],
    selectedCalendars: ["Personal", "Business", "Family", "Holiday", "ETC"],
    
  }),
  actions: {
    async fetchEvents() {
      const param = {
        type:this.selectedCalendars
      };
      const { data } = await axiosIns.post("/admin/event/list", param);
      return data.data.events;
    },
    async addEvent(event: NewEvent) {
      // return axios.post("/apps/calendar/events", { event });
      const type = event?.extendedProps?.calendar ?? "";
      const param = {
        title: event.title,
        type: type.charAt(0),
        url: event.url,
        start_date: moment(event.start).format('YYYY-MM-DD HH:mm:ss'),
        end_date: moment(event.end).format('YYYY-MM-DD HH:mm:ss'),
        is_allday: event.allDay,
      };
      const { data } = await axiosIns.post("/admin/event/create", param);
      console.log("create event", data.data);
      return data.data;
    },
    async updateEvent(event: Event) {
      
      const type = event?.extendedProps?.calendar ?? "";
      const param = {
        id: event.id,
        title: event.title,
        type: type.charAt(0),
        url: event.url,
        start_date: moment(event.start).format('YYYY-MM-DD HH:mm:ss'),
        end_date: moment(event.end).format('YYYY-MM-DD HH:mm:ss'),
        is_allday: event.allDay,
      };
      
      const { data } = await axiosIns.post("/admin/event/update", param);
      console.log("event", data.data);
      return data.data;
      // return axios.post(`/apps/calendar/events/${event.id}`, { event });
    },
    async removeEvent(eventId: string) {
      // return axios.delete(`/apps/calendar/events/${eventId}`);
      return axiosIns.get(`/admin/event/delete/${eventId}`);
    },
  },
});
