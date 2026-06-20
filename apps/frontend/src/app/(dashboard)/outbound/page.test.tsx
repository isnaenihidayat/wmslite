import { describe, it, expect, vi } from "vitest";
import { screen, waitFor } from "@testing-library/react";
import { renderWithQueryClient } from "@/test/render-with-providers";
import OutboundPage from "./page";

vi.mock("next-auth/react", () => ({
  useSession: () => ({
    data: { user: { admin: 1, type: 1, module: 4 } },
    status: "authenticated",
  }),
}));

vi.mock("@/lib/api/outbound.service", () => ({
  fetchOutboundList: vi.fn().mockResolvedValue({ data: [], total: 0, lastPage: 1 }),
}));

describe("OutboundPage", () => {
  it("renders without throwing and shows the Add button", async () => {
    renderWithQueryClient(<OutboundPage />);

    expect(screen.getByText("Outbound")).toBeTruthy();

    await waitFor(() => {
      expect(screen.getByText("0 total records")).toBeTruthy();
    });

    expect(screen.getByRole("button", { name: /add outbound/i })).toBeTruthy();
  });
});
