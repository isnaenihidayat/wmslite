"use client";

import { useEffect } from "react";
import { useSession } from "next-auth/react";
import { setAuthToken, clearAuthToken } from "@/lib/api/client";

/**
 * TokenSync — invisible component that syncs the NextAuth JWT access token
 * to sessionStorage so the axios client can pick it up for Bearer auth.
 */
export function TokenSync() {
  const { data: session, status } = useSession();

  useEffect(() => {
    if (status === "authenticated" && session?.user?.accessToken) {
      setAuthToken(session.user.accessToken as string);
    } else if (status === "unauthenticated") {
      clearAuthToken();
    }
  }, [session, status]);

  // This component renders nothing
  return null;
}
