import { isEmpty, isEmptyArray, isNullOrUndefined } from './index';

// 👉 Required Validator
export const requiredValidator = (value: unknown, fieldName: string) => {
  if (isNullOrUndefined(value) || isEmptyArray(value) || value === false)
    return `${fieldName} field is required`;

  return !!String(value).trim().length || `${fieldName} field is required`
}

// 👉 Email Validator
export const emailValidator = (value: unknown) => {
  if (isEmpty(value))
    return true

  const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/

  if (Array.isArray(value))
    return value.every(val => re.test(String(val))) || 'The Email field must be a valid email'

  return re.test(String(value)) || 'The Email field must be a valid email'
}

// 👉 new Password Validator
export const newPasswordVal = (value: string, target: string) =>
  value !== target || "Current and new passwords must be different";

// 👉 Password Validator
export const passwordValidator = (password: string) => {
  const regExp = /(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%&*()]).{8,}/

  const validPassword = regExp.test(password)

  return (
    // eslint-disable-next-line operator-linebreak
    validPassword ||
    'Field must contain at least one uppercase, lowercase, special character and digit with min 8 chars'
  )
}

// 👉 Confirm Password Validator
export const confirmedValidator = (value: string, target: string) =>

  value === target || 'The Confirm Password field confirmation does not match'

// 👉 Between Validator
export const betweenValidator = (value: unknown, min: number, max: number) => {
  const valueAsNumber = Number(value)

  return (Number(min) <= valueAsNumber && Number(max) >= valueAsNumber) || `Enter number between ${min} and ${max}`
}

// 👉 Integer Validator
export const integerValidator = (value: unknown) => {
  if (isEmpty(value))
    return true

  if (Array.isArray(value))
    return value.every(val => /^-?[0-9]+$/.test(String(val))) || 'This field must be an integer'

  return /^-?[0-9]+$/.test(String(value)) || 'This field must be an integer'
}

// 👉 Regex Validator
export const regexValidator = (value: unknown, regex: RegExp | string): string | boolean => {
  if (isEmpty(value))
    return true

  let regeX = regex
  if (typeof regeX === 'string')
    regeX = new RegExp(regeX)

  if (Array.isArray(value))
    return value.every(val => regexValidator(val, regeX))

  return regeX.test(String(value)) || 'The Regex field format is invalid'
}

// 👉 Alpha Validator
export const alphaValidator = (value: unknown) => {
  if (isEmpty(value))
    return true

  return /^[A-Z]*$/i.test(String(value)) || 'The Alpha field may only contain alphabetic characters'
}

// 👉 URL Validator
export const urlValidator = (value: unknown) => {
  if (isEmpty(value))
    return true

  const re = /^(http[s]?:\/\/){0,1}(www\.){0,1}[a-zA-Z0-9\.\-]+\.[a-zA-Z]{2,5}[\.]{0,1}/

  return re.test(String(value)) || 'URL is invalid'
}

// 👉 Length Validator
// for min and max character of a field
export const lengthValidator = (
  value: string,
  minLength: number,
  maxLength: number,
  field: string
) => {
  /**
   * @param value: value of the field
   * @param minLength: min length required
   * @param maxLength: max length required
   */
  if (isEmpty(value)) return true;

  if (value.length < minLength && minLength !== 0)
    return `The ${field} must be at least ${minLength} characters`;
  else if (value.length > maxLength)
    return `The ${field} may not be greater than ${maxLength} characters`;
};

// 👉 Alpha-dash Validator
export const alphaDashValidator = (value: unknown) => {
  if (isEmpty(value))
    return true

  const valueAsString = String(value)

  return /^[0-9A-Z_-]*$/i.test(valueAsString) || 'All Character are not valid'
}
export const validateDate = (value: unknown) => {
  // Convert the value to a Date object
  const selectedDate = new Date(String(value));
  const currentDate = new Date();

  // Normalize both dates to the start of the day
  selectedDate.setHours(0, 0, 0, 0);
  currentDate.setHours(0, 0, 0, 0);

  // Check if the date is invalid
  if (isNaN(selectedDate.getTime())) {
    return "Invalid date";
  }

  // Check if the selected date is today or a future date
  if (selectedDate.getTime() === currentDate.getTime()) {
    return true; // No validation message needed if the date is today
  }

  // if (selectedDate < currentDate) {
  //   return "Please select a future date"; // Validation message if the date is in the past
  // }

  return true; // Valid date if it's in the future
};


// utils/validation.ts
export const validateEndDate = (startDate: any, endDate: any): string | true => {
  if (startDate && endDate) {
    const start = new Date(startDate);
    const end = new Date(endDate);

    // Check if the dates are valid
    if (isNaN(start.getTime()) || isNaN(end.getTime())) {
      return 'Invalid date format.';
    }

    // Compare dates
    return end >= start ? true : 'End date must be greater than start date.';
  }

  return true; // Consider it valid if either date is not provided
};

  