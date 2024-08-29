import type { VerticalNavItems } from '@/@layouts/types';

const adminNavItems: VerticalNavItems = [
  {
    title: 'Dashboard',
    to: { name: 'admin-dashboard' },
    icon: { icon: 'tabler-smart-home' },
  },
  {
    title: 'Users',
    to: { name: 'admin-user-list' },
    icon: { icon: 'tabler-users' },
  },
  {
    title: 'Category',
    to: { name: 'admin-category-list' },
    icon: { icon: 'tabler-category' },
    
  },
  {
    title: 'SubCategory',
    to: { name: 'admin-subcategory-list' },
    icon: { icon: 'tabler-category-2' },
  },
  {
    title: 'Product',
    to: { name: 'admin-products-list' },
    icon: { icon: 'tabler-layout-grid' },
  },
  {
    title: 'Company',
    to: { name: 'admin-company-list' },
    icon: { icon: 'tabler-layout-grid' },
  },
  {
    title: 'Project',
    to: { name: 'admin-projects-list' },
    icon: { icon: 'tabler-layout-grid' },
  },
  {
    title: 'Calendar',
    to: { name: 'calendar' },
    icon: { icon: 'tabler-calendar' },
  },
];

const managerNavItems: VerticalNavItems = [
  {
    title: 'Dashboard',
    to: { name: 'admin-dashboard' },
    icon: { icon: 'tabler-smart-home' },
  },
  {
    title: 'Employees',
    to: { name: 'admin-user-list' },
    icon: { icon: 'tabler-users' },
  },
  {
    title: 'Projects',
    to: { name: 'admin-projects-list' },
    icon: { icon: 'tabler-layout-grid' },
  },
  // other manager routes
];

const customerNavItems: VerticalNavItems = [
  {
    title: 'Dashboard',
    to: { name: 'dashboard' },
    icon: { icon: 'tabler-smart-home' },
  },
 
  // other customer routes
];

export { adminNavItems, customerNavItems, managerNavItems };


// export default [
//   {
//     title: 'Dashboard',
//     to: { name: 'admin-dashboard' },
//     icon: { icon: 'tabler-smart-home' },
//   },
//   {
//     title: 'Users',
//     to: { name: 'admin-user-list' },
//     icon: { icon: 'tabler-users' },
//   },
//   {
//     title: 'Category',
//     to: { name: 'admin-category-list' },
//     icon: { icon: 'tabler-category' },
    
//   },
//   {
//     title: 'SubCategory',
//     to: { name: 'admin-subcategory-list' },
//     icon: { icon: 'tabler-category-2' },
//   },
//   {
//     title: 'Product',
//     to: { name: 'admin-products-list' },
//     icon: { icon: 'tabler-layout-grid' },
//   },
//   {
//     title: 'Company',
//     to: { name: 'admin-company-list' },
//     icon: { icon: 'tabler-layout-grid' },
//   },
//   {
//     title: 'Project',
//     to: { name: 'admin-projects-list' },
//     icon: { icon: 'tabler-layout-grid' },
//   },
// ] as VerticalNavItems
