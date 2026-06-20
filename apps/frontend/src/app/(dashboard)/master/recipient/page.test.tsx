import { describe, it, expect, vi } from "vitest";
import { screen, waitFor } from "@testing-library/react";
import { renderWithQueryClient } from "@/test/render-with-providers";
import RecipientPage from "./page";

vi.mock("@/lib/api/master.service", () => ({
  fetchRecipients: vi.fn().mockResolvedValue([]),
}));

describe("RecipientPage", () => {
  it("renders without throwing and shows the empty state", async () => {
    renderWithQueryClient(<RecipientPage />);

    expect(screen.getByText("Recipients")).toBeTruthy();

    await waitFor(() => {
      expect(screen.getByText("Belum ada data penerima.")).toBeTruthy();
    });
  });
});
