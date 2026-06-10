import NextAuth from "next-auth";
import CredentialsProvider from "next-auth/providers/credentials";

const LARAVEL_API = process.env.NEXT_PUBLIC_LARAVEL_API_URL || "http://localhost:8000/api";

export const { handlers, auth, signIn, signOut } = NextAuth({
  providers: [
    CredentialsProvider({
      name: "credentials",
      credentials: {
        email: { label: "Email", type: "email" },
        password: { label: "Password", type: "password" },
      },
      async authorize(credentials) {
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
              // Store Laravel Sanctum token
              accessToken: data.token,
              // User profile
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
      },
    }),
  ],
  callbacks: {
    async jwt({ token, user }) {
      if (user) {
        const u = user as unknown as Record<string, unknown>;
        token.accessToken = u.accessToken as string | undefined;
        token.user_id     = u.user_id as number | undefined;
        token.type        = u.type as number | undefined;
        token.admin       = u.admin as number | undefined;
        token.module      = u.module as number | undefined;
        token.status      = u.status as string | undefined;
      }
      return token;
    },
    async session({ session, token }) {
      if (token) {
        session.user.accessToken = token.accessToken as string;
        session.user.user_id     = token.user_id as number;
        session.user.type        = token.type as number;
        session.user.admin       = token.admin as number;
        session.user.module      = token.module as number;
        session.user.status      = token.status as string;
      }
      return session;
    },
  },
  pages: {
    signIn: "/login",
    error: "/login",
  },
  session: {
    strategy: "jwt",
    maxAge: 8 * 60 * 60, // 8 hours
  },
});
