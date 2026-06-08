// ===========================
// AUTH TYPES
// ===========================

export interface User {
  user_id: number;
  first_name: string;
  last_name: string;
  email_address: string;
  mobile_number: string | null;
  type: UserType;
  admin: 0 | 1;
  module: number;
  status: "active" | "pending" | "block" | "decline";
  token?: string;
}

export type UserType = 0 | 1 | 2 | 3;
// 0 = default, 1 = warehouse, 2 = custom/supervisor, 3 = read-only

// ===========================
// SHIPMENT TYPES
// ===========================

export type ShipmentStatus =
  | "inprogress"
  | "Custom Process"
  | "Warehouse in Transit"
  | "successful";

export type ShipMethod = "Air" | "Ocean" | "Land" | "Express" | "";

export interface Shipment {
  id: number;
  hawb: string;
  descr: string;
  product_category_id: number | null;
  product_category_name?: string;
  modality: string | null;
  delivery_id: string | null;
  qty: number | null;
  po: string | null;
  locator: string;
  docfile: string;
  checker: string;
  date_created: string;
  date_updated: string | null;
  status: ShipmentStatus;
  ship_method: ShipMethod;
  etd: string | null;
  eta: string | null;
  ata: string | null;
  pib_number: string | null;
  sppb_date: string | null;
  from_shipment: 0 | 1;
  created_by: number | null;
  updated_by: number | null;
}

// ===========================
// INBOUND TYPES
// ===========================

export type InboundStatus = "created" | "Warehouse in Transit" | "successful";

export interface InboundHeader {
  id: number;
  hawb: string;
  descr: string;
  product_category_id: number | null;
  product_category_name?: string;
  modality: string | null;
  delivery_id: number | null;
  qty: number | null;
  po: string | null;
  locator: string;
  docfile: string;
  checker: string;
  date_created: string;
  date_updated: string | null;
  status: InboundStatus;
  ship_method: ShipMethod;
  etd: string | null;
  eta: string | null;
  ata: string | null;
  pib_number: string | null;
  sppb_date: string | null;
  totalQtyReceived?: number;
  itemInDetail?: number;
  totalPick?: number;
  from_shipment: 0 | 1;
}

export interface InboundDetail {
  hawb: string;
  descr: string;
  loc: string;
  weight: number | null;
  long: number | null;
  wide: number | null;
  high: number | null;
  flag: 0 | 1;
  scan_time: string | null;
  date_created: string | null;
  date_updated: string | null;
}

// ===========================
// OUTBOUND TYPES
// ===========================

export type OutboundStatus = "created" | "inprogress" | "successful";

export interface OutboundHeader {
  id: number;
  qty: number | null;
  po: string | null;
  destination: string;
  checker: string;
  docfile: string | null;
  date_created: string;
  date_updated: string | null;
  status: OutboundStatus;
  delivery_id: number | null;
  transporter: string | null;
  created_by: number | null;
  updated_by: number | null;
}

export interface OutboundDetail {
  hawb: string;
  descr: string;
  loc: string;
  id: number;
  flag: 0 | 1;
  scan_time: string | null;
}

// ===========================
// MOVING TYPES
// ===========================

export interface Moving {
  id: number;
  hawb: string;
  hawb_descr: string;
  loc_before: string;
  loc_after: string;
  date_created: string;
  users: string;
}

// ===========================
// DEMO MOVEMENT TYPES
// ===========================

export type DemoMovementStatus =
  | "Requested"
  | "Approved"
  | "Done"
  | "Rejected";

export interface DemoMovement {
  id: number;
  ref: string;
  requested_by: string;
  from_loc: string;
  to_loc: string;
  status: DemoMovementStatus;
  checker: string | null;
  is_return: 0 | 1;
  returned_from: string | null;
  created_at: string;
  updated_at: string | null;
  created_by: number;
  updated_by: number | null;
}

// ===========================
// MASTER DATA TYPES
// ===========================

export interface Location {
  loc_id: number;
  loc_name: string;
  loc_descr: string;
}

export interface ProductCategory {
  id: number;
  name: string;
  created_at: string;
  updated_at: string | null;
  created_by: number;
}

export interface Recipient {
  id: number;
  name: string;
  email: string | null;
  created_at: string;
  updated_at: string | null;
  created_by: number;
}

export interface ApkChecker {
  id: number;
  name: string;
  username: string;
  last_login: string | null;
  token: string | null;
}

// ===========================
// API RESPONSE TYPES
// ===========================

/** Yii backend response format */
export interface YiiResponse<T = unknown> {
  code: 1 | 2;
  msg: string;
  details: T;
  on_update?: string;
}

/** Laravel backend response format (target) */
export interface ApiResponse<T = unknown> {
  data: T;
  message: string;
  status: "success" | "error";
}

/** Paginated response */
export interface PaginatedResponse<T> {
  data: T[];
  total: number;
  per_page: number;
  current_page: number;
  last_page: number;
}

/** DataTables server-side response (Yii format) */
export interface DataTablesResponse<T> {
  sEcho: number;
  iTotalRecords: number;
  iTotalDisplayRecords: number;
  aaData: T[];
}

// ===========================
// UI TYPES
// ===========================

export interface NavItem {
  title: string;
  href: string;
  icon?: React.ComponentType<{ className?: string }>;
  children?: NavItem[];
  requiredType?: UserType[];
  requireAdmin?: boolean;
}

export interface StatsCard {
  title: string;
  value: string | number;
  description?: string;
  trend?: "up" | "down" | "neutral";
  trendValue?: string;
  icon?: React.ComponentType<{ className?: string }>;
  color?: "blue" | "green" | "orange" | "red" | "purple";
}
