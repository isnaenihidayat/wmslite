import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Sign In",
  description: "Sign in to WMS Lite Warehouse Management System",
};

export default function AuthLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <div className="relative min-h-screen overflow-hidden">
      {/* Gradient background */}
      <div className="absolute inset-0 bg-gradient-to-br from-[oklch(0.20_0.06_252)] via-[oklch(0.27_0.07_250)] to-[oklch(0.16_0.05_252)]" />

      {/* Decorative blobs */}
      <div className="absolute -top-32 -left-32 h-96 w-96 rounded-full bg-[oklch(0.55_0.20_218/0.15)] blur-3xl" />
      <div className="absolute top-1/2 -right-32 h-80 w-80 rounded-full bg-[oklch(0.62_0.18_218/0.10)] blur-3xl" />
      <div className="absolute -bottom-20 left-1/4 h-72 w-72 rounded-full bg-[oklch(0.40_0.10_240/0.12)] blur-3xl" />

      {/* Grid pattern overlay */}
      <div
        className="absolute inset-0 opacity-[0.03]"
        style={{
          backgroundImage: `linear-gradient(oklch(0.9 0 0) 1px, transparent 1px), linear-gradient(90deg, oklch(0.9 0 0) 1px, transparent 1px)`,
          backgroundSize: "48px 48px",
        }}
      />

      {/* Content */}
      <div className="relative flex min-h-screen items-center justify-center p-4">
        {children}
      </div>
    </div>
  );
}
