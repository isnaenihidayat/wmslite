import { describe, it, expect, vi } from "vitest";
import { screen, waitFor } from "@testing-library/react";
import { renderWithQueryClient } from "@/test/render-with-providers";
import LocationsPage from "./page";

vi.mock("@/lib/api/master.service", () => ({
  fetchLocations: vi.fn().mockResolvedValue([]),
}));

describe("LocationsPage", () => {
  it("renders without throwing and shows the empty state", async () => {
    renderWithQueryClient(<LocationsPage />);

    expect(screen.getByText("Locations")).toBeTruthy();

    await waitFor(() => {
      expect(screen.getByText("Belum ada data lokasi.")).toBeTruthy();
    });
  });
});
