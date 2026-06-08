// NextAuth.js v5 type augmentation
import "next-auth";
import "next-auth/jwt";

declare module "next-auth" {
  interface Session {
    user: {
      id: string;
      name?: string | null;
      email?: string | null;
      image?: string | null;
      user_id: number;
      type: number;
      admin: number;
      module: number;
      status: string;
    };
  }
}

declare module "next-auth/jwt" {
  interface JWT {
    user_id?: number;
    type?: number;
    admin?: number;
    module?: number;
    status?: string;
  }
}
