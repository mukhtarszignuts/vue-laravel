import useUserData from "./useFetchUserData";
const { fetchUserData } = useUserData();

export default function useFormatting() {
  const projectStatus = ref<any[]>([
    {
      id: "I",
      name: "In Progress",
    },
    {
      id: "P",
      name: "In Planning",
    },
    {
      id: "C",
      name: "Completed",
    },
    {
      id: "R",
      name: "Rejected",
    },
    {
      id: "H",
      name: "On Hold",
    },
  ]);

  const projectVariant = (status: string) => {
    const statusOption = projectStatus.value.find(
      (option) => option.id === status
    );

    if (statusOption) {
      switch (status) {
        case "I":
          return { color: "success", text: statusOption.name };
        case "P":
          return { color: "warning", text: statusOption.name };
        case "C":
          return { color: "primary", text: statusOption.name };
        case "R":
          return { color: "error", text: statusOption.name };
        case "H":
          return { color: "dark", text: statusOption.name };
        default:
          return { color: "secondary", text: "Undefined" };
      }
    } else {
      return { color: "info", text: "Undefined" };
    }
  };
  const user = fetchUserData();

  // Define the additional role
  const additionalRoles = [
    { id: "A", name: "Admin" },
    { id: "M", name: "Manager" },
  ];

  const roles = ref<any[]>([
    {
      id: "C",
      name: "Client",
    },
    {
      id: "E",
      name: "Employee",
    },
  ]);

  // Computed property to return the updated roles based on user role
  const computedRoles = computed(() => {
    // Check user role and conditionally add the 'Admin' role
    if (user.value?.role === "A") {
      return [...roles.value, ...additionalRoles];
    }
    return roles.value;
  });

  const roleVariant = (role: string) => {
    const roleOption = computedRoles.value.find((option) => option.id === role);
    if (roleOption) {
      switch (role) {
        case "A":
          return { color: "dark", text: roleOption.name };
        case "M":
          return { color: "success", text: roleOption.name };
        case "E":
          return { color: "info", text: roleOption.name };
        case "C":
          return { color: "primary", text: roleOption.name };
        default:
          return { color: "secondary", text: "Undefined" };
      }
    } else {
      return { color: "secondary", text: role=='M'?'Manager':'Undefined' };
    }
  };

  // project
  const types = ref<any[]>([
    {
      id: "S",
      name: "Small",
    },
    {
      id: "M",
      name: "Medium",
    },
    {
      id: "L",
      name: "Large",
    },
  ]);

  // project type
  const typeVariant = (type: string) => {
    const typeOption = types.value.find((option) => option.id === type);

    if (typeOption) {
      switch (type) {
        case "S":
          return { color: "success", text: typeOption.name };
        case "M":
          return { color: "warning", text: typeOption.name };
        case "L":
          return { color: "error", text: typeOption.name };
        default:
          return { color: "secondary", text: "Undefined" };
      }
    } else {
      return { color: "secondary", text: "Undefined" };
    }
  };

  // user status
  const statusOptions = ref<any[]>([
    {
      id: "P",
      name: "Pending",
    },
    {
      id: "A",
      name: "Active",
    },
    {
      id: "I",
      name: "Rejected",
    },
  ]);

  const statusVariant = (status: string) => {
    const statusOption = statusOptions.value.find(
      (option) => option.id === status
    );

    if (statusOption) {
      switch (status) {
        case "A":
          return { color: "success", text: statusOption.name };
        case "P":
          return { color: "warning", text: statusOption.name };
        case "I":
          return { color: "error", text: statusOption.name };
        default:
          return { color: "secondary", text: "Undefined" };
      }
    } else {
      return { color: "secondary", text: "Undefined" };
    }
  };

  return {
    typeVariant,
    types,
    statusOptions,
    projectStatus,
    statusVariant,
    projectVariant,
    roles: computedRoles,
    roleVariant,
  };
}
