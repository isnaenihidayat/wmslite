import { describe, it, expect, vi } from "vitest";
import { screen, waitFor } from "@testing-library/react";
import { renderWithQueryClient } from "@/test/render-with-providers";
import DashboardPage from "./page";

vi.mock("next-auth/react", () => ({
  useSession: () => ({
    data: { user: { name: "Jane Doe", admin: 1, type: 1, module: 4 } },
    status: "authenticated",
  }),
}));

vi.mock("@/lib/api/dashboard.service", () => ({
  fetchDashboardStats: vi.fn().mockResolvedValue({
    totalShipments: 0,
    inboundInTransit: 0,
    activeOutbound: 0,
    successfulOutbound: 0,
    recentInbound: [],
    recentOutbound: [],
    statusBreakdown: [],
  }),
}));

describe("DashboardPage", () => {
  it("renders without throwing and shows the welcome heading", async () => {
    renderWithQueryClient(<DashboardPage />);

    expect(screen.getByText(/Selamat datang, Jane/)).toBeTruthy();

    await waitFor(() => {
      expect(screen.getAllByText("Belum ada data").length).toBeGreaterThan(0);
    });
  });
});
