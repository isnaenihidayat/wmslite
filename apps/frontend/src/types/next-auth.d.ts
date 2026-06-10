import "next-auth";
import "next-auth/jwt";

declare module "next-auth" {
  interface Session {
    user: {
      id: string;
      name?: string | null;
      email?: string | null;
      image?: string | null;
      // WMS custom fields
      accessToken: string;
      user_id: number;
      type: number;
      admin: number;
      module: number;
      status: string;
    };
  }

  interface User {
    id: string;
    name?: string | null;
    email?: string | null;
    // WMS custom fields
    accessToken?: string;
    user_id?: number;
    type?: number;
    admin?: number;
    module?: number;
    status?: string;
  }
}

declare module "next-auth/jwt" {
  interface JWT {
    accessToken?: string;
    user_id?: number;
    type?: number;
    admin?: number;
    module?: number;
    status?: string;
  }
}
