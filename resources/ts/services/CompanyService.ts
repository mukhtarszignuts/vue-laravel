import $http from "@/plugins/axios";

interface CompanyListRequest {
  sort_field:string,
  sort_order:string,
  page:number,
  per_page:number,
  search:any,
}

export const fetchCompanyList = async (params: CompanyListRequest) => {
  try {
    const {
      data: { data },
    } = await $http.post("admin/company/list", params);
    
    return data;
  } catch (e) {
    console.error("Error fetching company list:", e);
    return null;
  }
};
export const getCompanydata = async (id:number) => {
  try {
    const {
      data: { data },
    } = await $http.get(`admin/company/${id}`)
    return data;
  } catch (e) {
    console.error("Error get company data:", e);
    return null;
  }
};
export const statusChange = async (id:number) => {
  try {
    const { data } = await $http.get(`admin/company/status-change/${id}`)
    return data;
  } catch (e) {
    console.error("Error change status of company:", e);
    return null;
  }
};

export const deleteCompany = async (id: any) => {
  try {
    const path = `admin/company/delete/${id}`;
    const { data } = await $http.get(`${path}`);
    return data;
  } catch (e) {
    console.error("Error deleting company list:", e);
    return null;
  }
};

export const AddEditCompany = async (formData: any, isUpdate: boolean) => {
  try {
    if (isUpdate) {
      const  { data } = await $http.post("admin/company/update", formData, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });
      return data;
    } else {
      const { data } = await $http.post("admin/company/create", formData, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });
      return data;
    }
  } catch (e) {
    console.error("Error update company list:", e);
    return null;
  }
};
