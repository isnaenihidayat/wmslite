import { describe, it, expect, vi } from "vitest";
import { screen } from "@testing-library/react";
import { renderWithQueryClient } from "@/test/render-with-providers";
import ReportsPage from "./page";

vi.mock("next-auth/react", () => ({
  useSession: () => ({
    data: { user: { admin: 1, type: 1, module: 4 } },
    status: "authenticated",
  }),
}));

vi.mock("@/lib/api/dashboard.service", () => ({
  fetchReport: vi.fn().mockResolvedValue({
    columns: [],
    data: [],
    total: 0,
    lastPage: 1,
  }),
}));

describe("ReportsPage", () => {
  it("renders without throwing and shows the Load action before any report is triggered", () => {
    renderWithQueryClient(<ReportsPage />);

    expect(screen.getByText("Reports")).toBeTruthy();
    expect(screen.getByRole("button", { name: /load/i })).toBeTruthy();
    expect(
      screen.getByText(/Pilih parameter laporan dan klik/)
    ).toBeTruthy();
  });
});
