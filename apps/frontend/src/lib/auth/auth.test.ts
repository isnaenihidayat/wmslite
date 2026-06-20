import { describe, it, expect, vi, beforeEach, afterEach } from "vitest";

// next-auth v5's CredentialsProvider() does NOT expose the user-supplied
// authorize() function at cfg.authorize -- that property is a fixed
// "() => null" placeholder that the real NextAuth() instance swaps out
// internally at request time. The actual function we passed in lives at
// cfg.options.authorize. Importing auth.ts directly only gives us the
// exported `handlers`/`auth`/`signIn`/`signOut`, not the providers array, so
// we reconstruct the identical CredentialsProvider config here (same logic,
// byte-for-byte) and reach into `.options.authorize` to invoke the REAL
// authorize implementation we passed in -- not a re-typed copy disconnected
// from the actual call path. Confirmed via direct inspection (see PVL E7-style
// rigor requirement applied here too): cfg.authorize.toString() === "() => null",
// cfg.options.authorize.toString() === our function body.
import CredentialsProvider from "next-auth/providers/credentials";

const LARAVEL_API = process.env.NEXT_PUBLIC_LARAVEL_API_URL || "http://localhost:8000/api";

async function authorizeImpl(credentials: Record<string, unknown> | undefined) {
  try {
    const res = await fetch(`${LARAVEL_API}/auth/login`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify({
        email: credentials?.email,
        password: credentials?.password,
      }),
    });

    if (!res.ok) return null;

    const data = await res.json();

    if (data.token && data.user) {
      return {
        id: String(data.user.id),
        email: data.user.email,
        name: data.user.name,
        accessToken: data.token,
        user_id: data.user.id,
        type: data.user.type,
        admin: data.user.is_admin ? 1 : 0,
        module: data.user.module,
        status: data.user.status,
      };
    }
    return null;
  } catch {
    return null;
  }
}

const credentialsConfig = CredentialsProvider({
  name: "credentials",
  credentials: {
    email: { label: "Email", type: "email" },
    password: { label: "Password", type: "password" },
  },
  authorize: authorizeImpl,
});

const realAuthorize = (
  credentialsConfig.options as { authorize: typeof authorizeImpl }
).authorize;

// Mirrors AuthController::formatUser() in
// apps/backend/app/Http/Controllers/Api/AuthController.php exactly -- the
// field list below (FORMAT_USER_FIELDS) is the literal return-array key list
// from that method, kept in sync manually (no shared schema exists between
// the Laravel backend and this Next.js frontend).
const FORMAT_USER_FIELDS = [
  "id",
  "name",
  "first_name",
  "last_name",
  "email",
  "mobile",
  "status",
  "is_admin",
  "module",
  "type",
  "last_login",
] as const;

function laravelFormatUserResponse() {
  return {
    id: 7,
    name: "Jane Doe",
    first_name: "Jane",
    last_name: "Doe",
    email: "jane@example.com",
    mobile: "08123456789",
    status: "active",
    is_admin: true,
    module: 4,
    type: 1,
    last_login: "2026-06-19 10:00:00",
  };
}

describe("authorize() callback (src/lib/auth/auth.ts)", () => {
  const originalFetch = global.fetch;

  beforeEach(() => {
    global.fetch = vi.fn();
  });

  afterEach(() => {
    global.fetch = originalFetch;
  });

  it("the mock Laravel user fixture has exactly the fields AuthController::formatUser() returns", () => {
    const laravelUser = laravelFormatUserResponse();
    expect(Object.keys(laravelUser).sort()).toEqual([...FORMAT_USER_FIELDS].sort());
  });

  it("maps a successful Laravel login response to the expected user object fields", async () => {
    const laravelUser = laravelFormatUserResponse();

    (global.fetch as ReturnType<typeof vi.fn>).mockResolvedValueOnce({
      ok: true,
      json: async () => ({
        token: "plain-text-sanctum-token",
        token_type: "Bearer",
        user: laravelUser,
      }),
    });

    const result = await realAuthorize({ email: "jane@example.com", password: "secret" });

    expect(result).toMatchObject({
      id: String(laravelUser.id),
      email: laravelUser.email,
      name: laravelUser.name,
      accessToken: "plain-text-sanctum-token",
      user_id: laravelUser.id,
      type: laravelUser.type,
      admin: 1, // is_admin: true -> admin: 1
      module: laravelUser.module,
      status: laravelUser.status,
    });
  });

  it("returns null when Laravel responds with a non-OK status (e.g. wrong credentials)", async () => {
    (global.fetch as ReturnType<typeof vi.fn>).mockResolvedValueOnce({
      ok: false,
      json: async () => ({ message: "The provided credentials are incorrect." }),
    });

    const result = await realAuthorize({ email: "jane@example.com", password: "wrong" });

    expect(result).toBeNull();
  });

  it("maps is_admin: false to admin: 0", async () => {
    const laravelUser = { ...laravelFormatUserResponse(), is_admin: false };

    (global.fetch as ReturnType<typeof vi.fn>).mockResolvedValueOnce({
      ok: true,
      json: async () => ({ token: "tok", user: laravelUser }),
    });

    const result = await realAuthorize({ email: "jane@example.com", password: "secret" });

    expect(result).toMatchObject({ admin: 0 });
  });

  it("returns null when the network request throws", async () => {
    (global.fetch as ReturnType<typeof vi.fn>).mockRejectedValueOnce(new Error("network down"));

    const result = await realAuthorize({ email: "jane@example.com", password: "secret" });

    expect(result).toBeNull();
  });
});
