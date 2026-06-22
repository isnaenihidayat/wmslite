import { describe, it, expect, vi } from "vitest";
import { screen, waitFor } from "@testing-library/react";
import { renderWithQueryClient } from "@/test/render-with-providers";
import ProductCategoryPage from "./page";

vi.mock("next-auth/react", () => ({
  useSession: () => ({
    data: { user: { admin: 1, type: 1, module: 4, accessToken: "test-token" } },
    status: "authenticated",
  }),
}));

vi.mock("@/lib/api/master.service", () => ({
  fetchCategories: vi.fn().mockResolvedValue([]),
}));

describe("ProductCategoryPage", () => {
  it("renders without throwing and shows the empty state", async () => {
    renderWithQueryClient(<ProductCategoryPage />);

    expect(screen.getByText("Product Category")).toBeTruthy();

    await waitFor(() => {
      expect(screen.getByText("Belum ada data kategori.")).toBeTruthy();
    });
  });
});
