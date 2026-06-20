import { describe, it, expect, vi } from "vitest";
import { screen, waitFor } from "@testing-library/react";
import { renderWithQueryClient } from "@/test/render-with-providers";
import InboundPage from "./page";

vi.mock("next-auth/react", () => ({
  useSession: () => ({
    data: { user: { admin: 1, type: 1, module: 4 } },
    status: "authenticated",
  }),
}));

vi.mock("@/lib/api/inbound.service", () => ({
  fetchInboundList: vi.fn().mockResolvedValue({ data: [], total: 0, lastPage: 1 }),
}));

describe("InboundPage", () => {
  it("renders without throwing and shows the Add button", async () => {
    renderWithQueryClient(<InboundPage />);

    expect(screen.getByText("Inbound")).toBeTruthy();

    await waitFor(() => {
      expect(screen.getByText("0 total records")).toBeTruthy();
    });

    expect(screen.getByRole("button", { name: /add inbound/i })).toBeTruthy();
  });
});
