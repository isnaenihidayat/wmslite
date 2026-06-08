import NextAuth from "next-auth";
import CredentialsProvider from "next-auth/providers/credentials";
import axios from "axios";

const API_URL = process.env.NEXT_PUBLIC_API_URL || "http://localhost/wmslite/ajax";

export const { handlers, auth, signIn, signOut } = NextAuth({
  providers: [
    CredentialsProvider({
      name: "credentials",
      credentials: {
        email_address: { label: "Email", type: "email" },
        password: { label: "Password", type: "password" },
        type_module: { label: "Module", type: "text" },
      },
      async authorize(credentials) {
        try {
          const params = new URLSearchParams();
          params.append("action", "login");
          params.append("email_address", credentials?.email_address as string);
          params.append("password", credentials?.password as string);
          params.append("type_module", (credentials?.type_module as string) || "1");

          const response = await axios.post(API_URL, params, {
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            withCredentials: true,
          });

          const data = response.data;

          if (data.code === 1) {
            // Yii sets session — we store user info in JWT
            return {
              id: String(data.details?.user_id || "1"),
              email: credentials?.email_address as string,
              name: `${data.details?.first_name || ""} ${data.details?.last_name || ""}`.trim(),
              // Store full user object for role-based access
              user_id: data.details?.user_id,
              type: data.details?.type,
              admin: data.details?.admin,
              module: data.details?.module,
              status: data.details?.status,
              redirectUrl: data.details,
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
        const u = user as Record<string, unknown>;
        token.user_id = u.user_id as number | undefined;
        token.type = u.type as number | undefined;
        token.admin = u.admin as number | undefined;
        token.module = u.module as number | undefined;
        token.status = u.status as string | undefined;
      }
      return token;
    },
    async session({ session, token }) {
      if (token) {
        session.user.user_id = token.user_id as number;
        session.user.type = token.type as number;
        session.user.admin = token.admin as number;
        session.user.module = token.module as number;
        session.user.status = token.status as string;
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
