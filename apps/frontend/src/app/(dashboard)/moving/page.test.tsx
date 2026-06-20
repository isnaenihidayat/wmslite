import { describe, it, expect, vi } from "vitest";
import { screen, waitFor } from "@testing-library/react";
import { renderWithQueryClient } from "@/test/render-with-providers";
import MovingPage from "./page";

vi.mock("next-auth/react", () => ({
  useSession: () => ({
    data: { user: { admin: 1, type: 1, module: 4 } },
    status: "authenticated",
  }),
}));

vi.mock("@/lib/api/moving.service", () => ({
  fetchMovingList: vi.fn().mockResolvedValue({ data: [], total: 0, lastPage: 1 }),
}));

describe("MovingPage", () => {
  it("renders without throwing and shows the Add button", async () => {
    renderWithQueryClient(<MovingPage />);

    expect(screen.getByText("Moving")).toBeTruthy();

    await waitFor(() => {
      expect(screen.getByRole("button", { name: /create moving/i })).toBeTruthy();
    });
  });
});
