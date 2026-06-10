"use client";

import { useSession, signOut } from "next-auth/react";
import { useUIStore } from "@/stores/ui.store";
import { Avatar, AvatarFallback } from "@/components/ui/avatar";
import { Button } from "@/components/ui/button";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { Moon, Sun, LogOut, User, Menu } from "lucide-react";
import { useEffect } from "react";

interface HeaderProps {
  title?: string;
}

const USER_TYPE_LABEL: Record<number, string> = {
  0: "Default",
  1: "Warehouse",
  2: "Custom",
  3: "Read Only",
};

export function AppHeader({ title }: HeaderProps) {
  const { data: session } = useSession();
  const { darkMode, toggleDarkMode, setSidebarMobileOpen } = useUIStore();

  const name = session?.user?.name || session?.user?.email || "User";
  const initials = name
    .split(" ")
    .map((n) => n[0])
    .join("")
    .toUpperCase()
    .slice(0, 2);
  const userType = session?.user?.type ?? 0;
  const isAdmin = session?.user?.admin === 1;

  // Apply dark mode to html element
  useEffect(() => {
    const html = document.documentElement;
    if (darkMode) {
      html.classList.add("dark");
    } else {
      html.classList.remove("dark");
    }
  }, [darkMode]);

  return (
    <header className="flex h-14 items-center justify-between border-b bg-card px-4 shadow-sm">
      <div className="flex items-center gap-3">
        {/* Mobile menu toggle */}
        <Button
          variant="ghost"
          size="icon"
          className="md:hidden"
          onClick={() => setSidebarMobileOpen(true)}
        >
          <Menu className="h-5 w-5" />
        </Button>
        {title && (
          <h1 className="text-base font-semibold text-foreground">{title}</h1>
        )}
      </div>

      <div className="flex items-center gap-2">
        {/* Dark mode toggle */}
        <Button
          variant="ghost"
          size="icon"
          onClick={toggleDarkMode}
          aria-label="Toggle dark mode"
          className="text-muted-foreground hover:text-foreground"
        >
          {darkMode ? <Sun className="h-4 w-4" /> : <Moon className="h-4 w-4" />}
        </Button>

        {/* User menu */}
        <DropdownMenu>
            <DropdownMenuTrigger>
            <div
              role="button"
              tabIndex={0}
              className="flex items-center gap-2 h-9 px-2 rounded-md hover:bg-accent hover:text-accent-foreground cursor-pointer transition-colors"
            >
              <Avatar className="h-7 w-7">
                <AvatarFallback className="bg-primary text-primary-foreground text-xs font-semibold">
                  {initials}
                </AvatarFallback>
              </Avatar>
              <div className="hidden text-left sm:block">
                <p className="text-xs font-medium leading-none">{name}</p>
                <p className="text-[10px] text-muted-foreground">
                  {isAdmin ? "Admin" : USER_TYPE_LABEL[userType]}
                </p>
              </div>
            </div>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end" className="w-48">
            <DropdownMenuLabel className="text-xs text-muted-foreground font-normal">
              {session?.user?.email}
            </DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuItem>
              <User className="mr-2 h-4 w-4" />
              Profile
            </DropdownMenuItem>
            <DropdownMenuSeparator />
            <DropdownMenuItem
              className="text-destructive focus:text-destructive"
              onClick={() => signOut({ callbackUrl: "/login" })}
            >
              <LogOut className="mr-2 h-4 w-4" />
              Sign Out
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>
      </div>
    </header>
  );
}
