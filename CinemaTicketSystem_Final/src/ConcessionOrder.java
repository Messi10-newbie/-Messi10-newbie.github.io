import java.util.ArrayList;
import java.util.Objects;

public class ConcessionOrder {
    private final ArrayList<ConcessionItem> items;
    private final String customerName;

    public ConcessionOrder(String customerName) {
        this.customerName = customerName;
        this.items        = new ArrayList<>();
    }

    public void addItem(ConcessionItem item)          { items.add(item); }
    public ArrayList<ConcessionItem> getItems()       { return items; }
    public String getCustomerName()                   { return customerName; }
    public boolean isEmpty()                          { return items.isEmpty(); }

    public double getTotalCost() {
        double total = 0.0;
        for (ConcessionItem item : items) total += item.getTotalCost();
        return total;
    }

    @Override
    public String toString() {
        StringBuilder sb = new StringBuilder("=== Concession Order for " + customerName + " ===\n");
        for (ConcessionItem item : items) sb.append("  ").append(item.toString()).append("\n");
        sb.append("Total: RM").append(String.format("%.2f", getTotalCost()));
        return sb.toString();
    }

    @Override
    public int hashCode() { return Objects.hash(customerName); }

    @Override
    public boolean equals(Object obj) {
        if (this == obj) return true;
        if (!(obj instanceof ConcessionOrder)) return false;
        ConcessionOrder other = (ConcessionOrder) obj;
        return customerName.equals(other.customerName);
    }
}
