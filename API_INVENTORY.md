# API Inventory — WMS Lite
> Dihasilkan dari audit `AjaxController.php` (6951 baris) dan `ApiController.php`
> Digunakan sebagai referensi untuk membangun Laravel REST API

---

## Konvensi Response (Yii saat ini)

```json
{
  "code": 1,       // 1 = sukses, 2 = gagal
  "msg": "...",
  "details": "...",
  "on_update": ""
}
```

**Laravel Response Convention (Target):**
```json
{ "data": {}, "message": "...", "status": "success" }   // 200
{ "errors": {}, "message": "...", "status": "error" }    // 4xx/5xx
```

---

## 🔐 AUTH

| Action (Yii) | Laravel Endpoint | Method | Keterangan |
|---|---|---|---|
| `actionLogin` | `POST /api/auth/login` | POST | Email+password → Sanctum token |
| `actionForgotPassword` | `POST /api/auth/forgot-password` | POST | Send reset email |
| `actionResetPassword` | `POST /api/auth/reset-password` | POST | Token + new password |
| `ApiController::actionLogout` | `POST /api/auth/logout` | POST | Revoke token |
| `ApiController::actionLogin` | — | — | Mobile API login (APK) |
| `ApiController::actionChangePassword` | `PUT /api/auth/password` | PUT | Ganti password |
| `ApiController::actionGetAppSettings` | `GET /api/settings` | GET | Config aplikasi |

---

## 👤 USERS

| Action (Yii) | Laravel Endpoint | Method | Keterangan |
|---|---|---|---|
| `actionuserList` | `GET /api/users` | GET | List user + DataTable |
| `actionaddUser` | `POST /api/users` | POST | Buat user baru |
| `actionaddUser` (edit) | `PUT /api/users/{id}` | PUT | Update user |
| `actiongetUserInfo` | `GET /api/users/{id}` | GET | Info user |
| `actionapproveUser` | `PATCH /api/users/{id}/approve` | PATCH | Approve user |
| `actiondeclineUser` | `PATCH /api/users/{id}/decline` | PATCH | Decline user |
| `actionblockUser` | `PATCH /api/users/{id}/block` | PATCH | Block user |
| `actionDeleteRecords` (user) | `DELETE /api/users/{id}` | DELETE | Hapus user |

**User Roles:**
- `type=0` → Default/unknown  
- `type=1` → Warehouse  
- `type=2` → Custom (Supervisor)  
- `type=3` → Read Only  
- `admin=1` → Admin flag (override semua permission)  
- `module=1` → OTR module, `module=2` → Service module

---

## 📦 SHIPMENT

| Action (Yii) | Laravel Endpoint | Method | Keterangan |
|---|---|---|---|
| `actionshlist` | `GET /api/shipments` | GET | List shipment (paginated) |
| `actiongetShList` | — | — | DataTable feed shipment |
| `actionaddSh` | `POST /api/shipments` | POST | Buat shipment baru |
| `actionaddSh` (edit) | `PUT /api/shipments/{id}` | PUT | Update shipment |
| `actiongetShInfo` | `GET /api/shipments/{id}` | GET | Detail shipment |
| `actiondelShipment` | `DELETE /api/shipments/{id}` | DELETE | Hapus shipment |
| `actiongetKoliDetailSh` | `GET /api/shipments/{id}/items` | GET | Detail koli shipment |
| `actionupdateKoliSh` | `PUT /api/shipments/{hawb}/items/{koli}` | PUT | Update koli |
| `actiongetsearch` | `GET /api/shipments/search` | GET | Search shipment |
| `ApiController::actionGetShipment` | `GET /api/mobile/shipments` | GET | Mobile: list shipment |

**Shipment Status Flow:**
```
(new) → "inprogress" → "Custom Process" → "Warehouse in Transit" → "successful"
```

**Fields Penting:**
- `hawb` (UNIQUE KEY) — Air Waybill Number
- `delivery_id` — SSO Delivery ID
- `ship_method` — Air/Ocean/Land/Express
- `etd`, `eta`, `ata`, `sppb_date`, `pib_number`
- `product_category_id`, `modality`, `po`

---

## 📥 INBOUND (OTR Module)

| Action (Yii) | Laravel Endpoint | Method | Keterangan |
|---|---|---|---|
| `actioninlist` | `GET /api/inbounds` | GET | List inbound (paginated) |
| `actiongetInList` | — | — | DataTable feed |
| `actionaddIn` | `POST /api/inbounds` | POST | Buat inbound dari shipment |
| `actionaddIn` (edit) | `PUT /api/inbounds/{id}` | PUT | Update inbound |
| `actiongetInInfo` | `GET /api/inbounds/{id}` | GET | Detail inbound |
| `actiondelInbound` | `DELETE /api/inbounds/{id}` | DELETE | Hapus inbound |
| `actiongetHawbDetail1` | `GET /api/inbounds/{id}/items` | GET | Item/koli detail |
| `actiongetKoliDetail` | `GET /api/inbounds/{hawb}/koli` | GET | Detail koli |
| `actionupdateKoli` | `PUT /api/inbounds/{hawb}/koli/{id}` | PUT | Update koli (scan) |
| `actionsendLoc` | `POST /api/inbounds/{hawb}/putaway` | POST | Putaway item ke lokasi |
| `actiongetLotQRCode` | `GET /api/inbounds/{hawb}/qr` | GET | QR Code data |
| `actiongetQRAll` | `GET /api/inbounds/{hawb}/qr/all` | GET | QR Code semua koli |
| `actiongetQROne` | `GET /api/inbounds/{hawb}/qr/{koli}` | GET | QR Code satu koli |
| `ApiController::actionGetInbound` | `GET /api/mobile/inbounds` | GET | Mobile: list |
| `ApiController::actionGetInById` | `GET /api/mobile/inbounds/{id}` | GET | Mobile: detail |
| `ApiController::actionUpdateInbound` | `PUT /api/mobile/inbounds/{id}` | PUT | Mobile: update |

**Inbound Status Flow:**
```
"created" → "Warehouse in Transit" → "successful"
```

---

## 📥 INBOUND (Service/Schenker Module)

| Action (Yii) | Laravel Endpoint | Method | Keterangan |
|---|---|---|---|
| `actioninlists` | `GET /api/service/inbounds` | GET | List inbound service |
| `actiongetInLists` | — | — | DataTable |
| `actionaddIns` | `POST /api/service/inbounds` | POST | Buat inbound service |
| `actiongetInInfos` | `GET /api/service/inbounds/{id}` | GET | Detail |
| `actiondelInbounds` | `DELETE /api/service/inbounds/{id}` | DELETE | Hapus |
| `actiongetInsList` | `GET /api/service/inbounds/list` | GET | List khusus service |
| `actionsendLocs` | `POST /api/service/inbounds/{hawb}/putaway` | POST | Putaway |
| `actiongetLotList` | `GET /api/service/inbounds/{hawb}/lots` | GET | List lot |
| `actionGetAvailableLotSc` | `GET /api/service/lots/available` | GET | Available lots |

---

## 📤 OUTBOUND (OTR Module)

| Action (Yii) | Laravel Endpoint | Method | Keterangan |
|---|---|---|---|
| `actionoutlist` | `GET /api/outbounds` | GET | List outbound |
| `actiongetOutList` | — | — | DataTable feed |
| `actionaddOut` | `POST /api/outbounds` | POST | Buat outbound |
| `actionaddOut` (edit) | `PUT /api/outbounds/{id}` | PUT | Update |
| `actiongetOutInfo` | `GET /api/outbounds/{id}` | GET | Detail |
| `actiondelOutbound` | `DELETE /api/outbounds/{id}` | DELETE | Hapus (kembalikan flag inbound) |
| `actiongetPicList` | `GET /api/outbounds/{id}/picking` | GET | Picking list |
| `actionsendPush` | `POST /api/outbounds/{id}/push` | POST | Push outbound |
| `actiongetDO` | `GET /api/outbounds/{id}/do` | GET | Delivery Order |
| `ApiController::actionGetOutbound` | `GET /api/mobile/outbounds` | GET | Mobile |
| `ApiController::actionSendOut` | `POST /api/mobile/outbounds/{id}/send` | POST | Mobile: send |
| `ApiController::actionOutDetail` | `GET /api/mobile/outbounds/{id}` | GET | Mobile: detail |

**Outbound Status Flow:**
```
"created" → "inprogress" → "successful"
```

---

## 📤 OUTBOUND SCHENKER

| Action (Yii) | Laravel Endpoint | Method | Keterangan |
|---|---|---|---|
| `actionoutlistschenker` | `GET /api/service/outbounds` | GET | List Schenker |
| `actiongetOutListSchenker` | — | — | DataTable |
| `actionaddOutSchenker` | `POST /api/service/outbounds` | POST | Buat |
| `actiongetOutInfoSchenker` | `GET /api/service/outbounds/{id}` | GET | Detail |
| `actiondelOutboundSchenker` | `DELETE /api/service/outbounds/{id}` | DELETE | Hapus |
| `actiongetPicListSchenker` | `GET /api/service/outbounds/{id}/picking` | GET | Picking |
| `actiontryInsertSchenker` | `POST /api/service/outbounds/{id}/insert` | POST | Insert items |
| `actionGetDetailSc` | `GET /api/service/outbounds/{id}/detail` | GET | Detail Schenker |
| `actionGetPickSc` | `GET /api/service/outbounds/{id}/pick` | GET | Pick detail |
| `actionGetSkuSchenker` | `GET /api/service/sku` | GET | SKU list |
| `actionGetQtyDetailSchenker` | `GET /api/service/outbounds/{id}/qty` | GET | Qty detail |
| `actionGetPartSchenker` | `GET /api/service/parts` | GET | Part list |

---

## 🔄 MOVING / STOCK MOVEMENT

| Action (Yii) | Laravel Endpoint | Method | Keterangan |
|---|---|---|---|
| `actionmovList` | `GET /api/movings` | GET | List moving |
| `actionmovLoc` | `POST /api/movings` | POST | Buat moving |
| `actiongetHawbMoving` | `GET /api/movings/hawb` | GET | HAWB lookup |
| `actionmovLists` | `GET /api/service/movings` | GET | Moving service |
| `actionmovLocs` | `POST /api/service/movings` | POST | Moving service |

---

## 🚗 DEMO MOVEMENT

| Action (Yii) | Laravel Endpoint | Method | Keterangan |
|---|---|---|---|
| `actionDemo_Movement_List` | `GET /api/movements` | GET | List movement |
| `actionDemo_Movement_List_Detail` | `GET /api/movements/{id}/detail` | GET | Detail movement |
| `actiongetHawbAddDemoMovement` | `GET /api/movements/hawb-lookup` | GET | HAWB lookup |
| `actiongetHawbDetailDemo` | `GET /api/movements/{id}/hawb-detail` | GET | HAWB detail |
| `actiongetHawbDetailDemoEdit` | `GET /api/movements/{id}/hawb-detail-edit` | GET | Edit mode |
| `ApiController::actionGetDemoMovement` | `GET /api/mobile/movements` | GET | Mobile |
| `ApiController::actionGetDemoMovementDetail` | `GET /api/mobile/movements/{id}` | GET | Mobile detail |
| `ApiController::actionValidateDemoMovement` | `POST /api/mobile/movements/{id}/validate` | POST | Validate |

**Movement Status Flow:**
```
"Requested" → "Approved" → "Done" | "Rejected"
```

---

## 📍 LOCATION

| Action (Yii) | Laravel Endpoint | Method | Keterangan |
|---|---|---|---|
| `actionloclist` | `GET /api/locations` | GET | List lokasi |
| `actionaddLoc` | `POST /api/locations` | POST | Tambah lokasi |
| `actionaddLoc` (edit) | `PUT /api/locations/{id}` | PUT | Update lokasi |
| `actiongetLocInfo` | `GET /api/locations/{id}` | GET | Detail lokasi |
| `actionDeleteRecords` (loc) | `DELETE /api/locations/{id}` | DELETE | Hapus |
| `actiongetLoc` | `GET /api/locations/available` | GET | Available locations |
| `actiongetLocPart` | `GET /api/locations/parts` | GET | Lokasi per part |
| `ApiController::actionGetAllLoc` | `GET /api/mobile/locations` | GET | Mobile: all locs |
| `ApiController::actionGetLoc` | `GET /api/mobile/locations/available` | GET | Mobile: available |
| `ApiController::actionUpdateLoc` | `PUT /api/mobile/locations/{id}` | PUT | Mobile: update |

---

## 🏷️ MASTER DATA

### Product Category
| Action (Yii) | Laravel Endpoint | Method |
|---|---|---|
| `actionProduct_Category_List` | `GET /api/product-categories` | GET |
| (via views) | `POST /api/product-categories` | POST |
| (via views) | `PUT /api/product-categories/{id}` | PUT |
| (via views) | `DELETE /api/product-categories/{id}` | DELETE |

### Recipient
| Action (Yii) | Laravel Endpoint | Method |
|---|---|---|
| `actionRecipient_List` | `GET /api/recipients` | GET |
| (via views) | `POST /api/recipients` | POST |
| (via views) | `PUT /api/recipients/{id}` | PUT |
| (via views) | `DELETE /api/recipients/{id}` | DELETE |

### APK Checker
| Action (Yii) | Laravel Endpoint | Method |
|---|---|---|
| `actionapkList` | `GET /api/apk` | GET |
| `actionaddApk` | `POST /api/apk` | POST |
| `actionaddApk` (edit) | `PUT /api/apk/{id}` | PUT |
| `actiongetApkInfo` | `GET /api/apk/{id}` | GET |
| (via delete) | `DELETE /api/apk/{id}` | DELETE |

---

## 📊 REPORTS

| Action (Yii) | Laravel Endpoint | Method | Keterangan |
|---|---|---|---|
| `actiongetReportsSh` | `GET /api/reports/shipments` | GET | Report shipment |
| `actiongetReportsIn` | `GET /api/reports/inbound` | GET | Report inbound |
| `actiongetReportsOut` | `GET /api/reports/outbound` | GET | Report outbound |
| `actiongetReportsInv` | `GET /api/reports/inventory` | GET | Report inventory |
| `actiongetReportsInv2` | `GET /api/reports/inventory/v2` | GET | Inventory alt |
| (export) | `GET /api/reports/{type}/export` | GET | Excel export |

**Report Params:** `start_date`, `end_date`, `type`

---

## ⚙️ SETTINGS

| Action (Yii) | Laravel Endpoint | Method | Keterangan |
|---|---|---|---|
| `actiongeneralSettings` | `GET /api/admin/settings` | GET | Get settings |
| `actiongeneralSettings` (save) | `PUT /api/admin/settings` | PUT | Save settings |

**Settings Keys (el_option table):**
- `company_name`, `company_logo`, `client_logo`, `map_lat`, `map_lng`

---

## 🔎 LOOKUP / HELPER

| Action (Yii) | Laravel Endpoint | Method | Keterangan |
|---|---|---|---|
| `actiongetHawb` | `GET /api/lookup/hawb` | GET | Cari HAWB |
| `actiongetHawbSchenker` | `GET /api/lookup/hawb/schenker` | GET | HAWB Schenker |
| `actiongetHawbs` | `GET /api/lookup/hawbs` | GET | Multi HAWB |
| `actiongetParts` | `GET /api/lookup/parts` | GET | List parts |
| `actiongetpart` | `GET /api/lookup/part` | GET | Single part |
| `actiongetpartdesc` | `GET /api/lookup/part/desc` | GET | Part description |
| `actionValidateDeliveryId` | `POST /api/validate/delivery-id` | POST | Validate SSO ID |
| `actiongetShsList` | `GET /api/lookup/shipments` | GET | Shipment list untuk dropdown |

---

## 📱 MOBILE API (ApiController)

| Action | Endpoint | Keterangan |
|---|---|---|
| `actionLogin` | `POST /api/mobile/auth/login` | APK login (md5 password!) |
| `actionLogout` | `POST /api/mobile/auth/logout` | Logout |
| `actionGetAppSettings` | `GET /api/mobile/settings` | App config |
| `actionGetInbound` | `GET /api/mobile/inbounds` | Inbound list |
| `actionGetShipment` | `GET /api/mobile/shipments` | Shipment list |
| `actionGetOutbound` | `GET /api/mobile/outbounds` | Outbound list |
| `actionSendOut` | `POST /api/mobile/outbounds/send` | Konfirmasi outbound |
| `actionUpdateLoc` | `PUT /api/mobile/locations` | Update lokasi |
| `actionGetReceipt` | `GET /api/mobile/receipt` | Receipt data |

> ⚠️ **Catatan**: Mobile API saat ini menggunakan md5 password (TIDAK AMAN). Laravel akan menggunakan bcrypt + Sanctum token.

---

## 📈 Summary Count

| Kategori | Jumlah Laravel Endpoint |
|---|---|
| Auth | 7 |
| Users | 8 |
| Shipment | 10 |
| Inbound OTR | 14 |
| Inbound Service | 9 |
| Outbound OTR | 12 |
| Outbound Schenker | 12 |
| Moving | 6 |
| Demo Movement | 8 |
| Location | 10 |
| Master Data | 12 |
| Reports | 6 |
| Settings | 2 |
| Lookup/Helper | 8 |
| Mobile API | 10 |
| **Total** | **~134 endpoints** |
