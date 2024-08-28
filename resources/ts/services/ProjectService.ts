import $http from "@/plugins/axios";

interface ProjectListRequest {
  sort_field:string,
  sort_order:string,
  page:number,
  per_page:number,
  search: any,
  status: string | undefined,
  type: string | undefined,
  start_date: string | any,
  end_date: string | any,
}

export const fetchProjectList = async (params: ProjectListRequest) => {
  try {
    const {
      data: { data },
    } = await $http.post("admin/project/list", params);
    
    return data;
  } catch (e) {
    console.error("Error fetching project list:", e);
    return null;
  }
};
export const getProjectdata = async (id:number) => {
  try {
    const {
      data: { data },
    } = await $http.get(`admin/project/${id}`)
    return data;
  } catch (e) {
    console.error("Error get project data:", e);
    return null;
  }
};


export const deleteProject = async (id: any) => {
  try {
    const path = `admin/project/delete/${id}`;
    const { data } = await $http.get(`${path}`);
    return data;
  } catch (e) {
    console.error("Error deleting project list:", e);
    return null;
  }
};

export const AddEditProject = async (formData: any, isUpdate: boolean) => {
  try {
    if (isUpdate) {
      const  { data } = await $http.post("admin/project/update", formData, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });
      return data;
    } else {
      const { data } = await $http.post("admin/project/create", formData, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });
      return data;
    }
  } catch (e) {
    console.error("Error update project list:", e);
    return null;
  }
};
