import { auth } from "@/lib/auth/auth";
import { SessionProvider } from "next-auth/react";
import { AppSidebar } from "@/components/layout/sidebar";
import { AppHeader } from "@/components/layout/header";
import { TooltipProvider } from "@/components/ui/tooltip";
import { Toaster } from "@/components/ui/sonner";
import { TokenSync } from "@/components/auth/token-sync";

export default async function DashboardLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  const session = await auth();

  return (
    <SessionProvider session={session}>
      <TokenSync />
      <TooltipProvider>
        <div className="flex h-screen overflow-hidden bg-background">
          {/* Sidebar */}
          <AppSidebar />

          {/* Main content */}
          <div className="flex flex-1 flex-col overflow-hidden">
            <AppHeader />
            <main className="flex-1 overflow-y-auto custom-scrollbar p-6">
              {children}
            </main>
          </div>
        </div>
        <Toaster richColors position="top-right" />
      </TooltipProvider>
    </SessionProvider>
  );
}
