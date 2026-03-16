# Update the .env file

The file location is
```
/etc/docker/api/{env:dev|prod}/.env
```

# Run the container

docker run --name maboo_api_overhaul -p 8010:80 -d lucienozandry/maboo_api_overhaul:latest

docker run --name maboo_api_overhaul -p 8010:80 -e APP_URL=http://102.16.254.6:8010 -v /etc/docker/api/dev/storage:/var/www/html/storage -v /etc/docker/api/dev/.env:/var/www/html/.env -d lucienozandry/maboo_api_overhaul:latest

# Run typesense container

```
  services:
  typesense:
    image: typesense/typesense:30.1
    restart: on-failure
    ports:
      - "8108:8108"
    volumes:
      - ./typesense-data:/data
    command: '--data-dir /data --api-key=xyz --enable-cors'
```

# Import models for typesense indexation

```
php artisan scout:import "App\Models\Product"
```

# API Documentation for Frontend Developers

This document provides comprehensive information about the available API functions for interacting with the backend services. All functions use the `appFetch` utility and return promises.

## Authentication

### `getEmailInfo(email: string)`
Checks if an email address is already registered.

**Parameters:**
- `email` - Email address to check

**Returns:**
```typescript
{ is_taken: boolean }
```

**Example:**
```typescript
const { is_taken } = await getEmailInfo("user@example.com");
```

---

### `logInWithEmail(data)`
Authenticates a user with email and password.

**Parameters:**
```typescript
{
  email: FormDataEntryValue,
  password: FormDataEntryValue
}
```

**Returns:**
```typescript
{
  auth: User,
  token: string
}
```

**Example:**
```typescript
const { auth, token } = await logInWithEmail({
  email: "user@example.com",
  password: "password123"
});
```

---

### `registerUser(data)`
Creates a new user account.

**Parameters:**
```typescript
{
  email: FormDataEntryValue,
  password: FormDataEntryValue,
  password_confirmation: FormDataEntryValue,
  name: string
}
```

**Returns:**
```typescript
{
  auth: User,
  token: string
}
```

**Example:**
```typescript
const { auth, token } = await registerUser({
  email: "newuser@example.com",
  password: "securepass",
  password_confirmation: "securepass",
  name: "John Doe"
});
```

---

### `getAuthUser()`
Retrieves the currently authenticated user's information.

**Returns:**
```typescript
{ user: User }
```

---

### `updateAuthUser(payload)`
Updates the authenticated user's profile information.

**Parameters:**
```typescript
{
  name?: string,
  email?: string,
  password?: string,
  password_confirmation?: string,
  current_password?: string
}
```

**Returns:**
```typescript
{ user: User }
```

---

### `sendEmailVerificationCode()`
Sends a verification code to the user's email address.

**Returns:**
```typescript
{ link_sent: boolean }
```

---

### `attemptEmailVerification(code)`
Verifies a user's email address using the provided code.

**Parameters:**
- `code` - Verification code received via email

**Returns:**
```typescript
{ user: User }
```

---

### `sendPasswordResetLink(email)`
Sends a password reset link to the specified email address.

**Parameters:**
- `email` - Email address for password reset

**Returns:**
```typescript
{ link_sent: boolean }
```

---

### `resetPassword(payload)`
Resets the user's password using a reset token.

**Parameters:**
```typescript
FormData | {
  password: FormDataEntryValue,
  password_confirmation: FormDataEntryValue,
  token: FormDataEntryValue
}
```

**Returns:**
```typescript
{
  user: User,
  token: string
}
```

---

## Products

### `getProducts(params?)`
Retrieves a list of products with optional filtering and includes.

**Parameters:**
```typescript
ProductQueryParams (optional)
```

By default includes: `variants`, `images`, `category`

**Returns:**
```typescript
{ products: Product[] }
```

**Example:**
```typescript
const { products } = await getProducts({
  where: { status: 'active' },
  limit: 10
});
```

---

### `getProduct(slug: string)`
Retrieves a single product by its slug.

**Parameters:**
- `slug` - Product identifier slug

**Returns:**
```typescript
{ product: Product }
```

**Example:**
```typescript
const { product } = await getProduct("blue-shirt-xl");
```

---

### `searchProducts(keywords: string)`
Searches for products matching the provided keywords.

**Parameters:**
- `keywords` - Search terms

**Returns:**
```typescript
{ products: Product[] }
```

Automatically includes: `category`, `variants`, `images`

**Example:**
```typescript
const { products } = await searchProducts("summer dress");
```

---

### `getCategories()`
Retrieves all product categories.

**Returns:**
```typescript
{ categories: Category[] }
```

---

## Shopping Cart

### `addVariantToCart(payload)`
Adds a product variant to the user's cart.

**Parameters:**
```typescript
{
  variant_id: number,
  count: number
}
```

**Returns:**
```typescript
{ cart_item: CartItem }
```

**Example:**
```typescript
const { cart_item } = await addVariantToCart({
  variant_id: 42,
  count: 2
});
```

---

### `getCartItems(options?)`
Retrieves cart items with optional filtering.

**Parameters:**
```typescript
{
  where?: WhereConditions<CartItem>,
  whereIn?: WhereInConditions
}
```

**Returns:**
```typescript
{ cart_items: CartItem[] }
```

**Example:**
```typescript
const { cart_items } = await getCartItems({
  where: { status: 'active' }
});
```

---

### `updateCartItem(cartItemId, payload)`
Updates the quantity of a cart item.

**Parameters:**
- `cartItemId` - ID of the cart item
- `payload` - `{ count: number }`

**Returns:**
```typescript
{ cart_item: CartItem }
```

**Example:**
```typescript
const { cart_item } = await updateCartItem(5, { count: 3 });
```

---

### `removeCartItem(cartItemId)`
Removes an item from the cart.

**Parameters:**
- `cartItemId` - ID of the cart item to remove

**Returns:** Empty response

---

## Addresses

### `getAuthAddresses()`
Retrieves all addresses for the authenticated user.

**Returns:**
```typescript
{ addresses: Address[] }
```

---

### `createAddress(payload)`
Creates a new address for the user.

**Parameters:**
- `payload` - `FormData` containing address information

**Returns:**
```typescript
{
  address: Address,
  user: User
}
```

---

### `updateAddress(id, payload)`
Updates an existing address.

**Parameters:**
- `id` - Address ID
- `payload` - `FormData` with updated address information

**Returns:**
```typescript
{
  address: Address,
  user: User
}
```

---

### `removeAddresses(ids)`
Deletes one or more addresses.

**Parameters:**
- `ids` - Array of address IDs to delete

**Returns:**
```typescript
{ deleted: number }
```

**Example:**
```typescript
const { deleted } = await removeAddresses([1, 2, 3]);
```

---

## Orders

### `createOrder(payload)`
Creates a new order from cart items.

**Parameters:**
```typescript
{
  cart_item_ids: number[],
  address_id: number,
  coupon_id?: number
}
```

**Returns:**
```typescript
{ order: Order }
```

**Example:**
```typescript
const { order } = await createOrder({
  cart_item_ids: [1, 2, 3],
  address_id: 5,
  coupon_id: 10
});
```

---

### `getOrders()`
Retrieves all orders for the authenticated user.

**Returns:**
```typescript
{ orders: Order[] }
```

Orders are sorted by `updated_at` in descending order and include `cart_items` and `transactions`.

---

### `getOrder(uuid)`
Retrieves a single order by its UUID.

**Parameters:**
- `uuid` - Order UUID

**Returns:**
```typescript
{ order: Order }
```

Includes: `cart_items`, `transactions`, `shipments`

---

### `deleteOrder(uuid)`
Deletes an order.

**Parameters:**
- `uuid` - Order UUID

**Returns:**
```typescript
{ message: string }
```

---

## Coupons

### `getCouponFromCode(code)`
Retrieves coupon information using a coupon code.

**Parameters:**
- `code` - Coupon code string

**Returns:**
```typescript
{ coupon: Coupon }
```

**Example:**
```typescript
const { coupon } = await getCouponFromCode("SUMMER2024");
```

---

## Transactions

### `createTransaction(data)`
Creates a payment transaction for an order.

**Parameters:**
```typescript
{
  method: Transaction['method'],
  order_uuid: Transaction['order_uuid'],
  amount: Transaction['amount']
}
```

**Returns:**
```typescript
{ transaction: Transaction }
```

---

## Notifications

### `getNotifications()`
Retrieves all notifications for the user.

**Returns:**
```typescript
{
  notifications: AppNotification[],
  unread: AppNotification[],
  unread_count: number
}
```

---

### `getUnreadNotifications()`
Retrieves only unread notifications.

**Returns:**
```typescript
{
  notifications: AppNotification[],
  count: number
}
```

---

### `clearReadNotifications()`
Removes all read notifications.

**Returns:**
```typescript
{ message: string }
```

---

### `markAllNotificationsAsRead()`
Marks all notifications as read.

**Returns:**
```typescript
{ message: string }
```

---

### `removeNotification(id)`
Deletes a specific notification.

**Parameters:**
- `id` - Notification ID

**Returns:**
```typescript
{ message: string }
```

---

### `markNotificationAsRead(id)`
Marks a specific notification as read.

**Parameters:**
- `id` - Notification ID

**Returns:**
```typescript
{
  message: string,
  notification: AppNotification
}
```

---

## Type Definitions

The API uses the following TypeScript types (ensure these are defined in your project):

- `User` - User account information
- `Product` - Product details
- `CartItem` - Shopping cart item
- `Address` - Delivery/billing address
- `Order` - Order information
- `Coupon` - Discount coupon
- `Transaction` - Payment transaction
- `AppNotification` - User notification
- `Category` - Product category
- `ProductQueryParams` - Product query parameters
- `WhereConditions` - Query filter conditions
- `WhereInConditions` - "IN" query conditions

## Error Handling

All API functions return promises and should be wrapped in try-catch blocks for proper error handling:

```typescript
try {
  const { products } = await getProducts();
  // Handle success
} catch (error) {
  // Handle error
  console.error('Failed to fetch products:', error);
}
```